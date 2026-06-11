"""Parser de texto OCR de cupons/notas fiscais brasileiras.

Extrai itens (descricao, quantidade, preco unitario) a partir do texto
bruto retornado pelo Tesseract. Cobre três layouts comuns:

1. NFC-e:  "003277 PRODUTO 1,000 CX 15,10 15,10"  (qtd UN unit total)
2. Cupom com "x":  "FEIJAO 2 UN x 8,50 17,00"
3. ECF em duas linhas:
       "001 21259003 DESOD SANIT PINH-SANIFECT -35G"
       "1 x 1,09 717,00% 1,09"

O OCR corrompe caracteres com frequência ("x" vira "%", "1,000" vira
"10000", "22,00" vira "2200"); as heurísticas abaixo corrigem os casos
recorrentes usando o total da linha como prova (qtd = total / unitário).
"""

from __future__ import annotations

import re

# Linhas que nunca são itens de compra.
_IGNORAR = re.compile(
    r"(CNPJ|CPF|TOTAL|SUBTOTAL|TROCO|DINHEIRO|CART[AÃ]O|D[EÉ]BITO|CR[EÉ]DITO"
    r"|PIX|EXTRATO|CUPOM|FISCAL|SAT\b|NFC|CHAVE|ACESSO|OPERADOR|CAIXA"
    r"|A\s*PAGAR|DESCONTO|ACR[EÉ]SCIMO|IMPOSTO|TRIBUT|LEI\b|FONTE\b"
    r"|OBRIGADO|VOLTE\s+SEMPRE|CONSUMIDOR|PROTOCOLO|AUTORIZA|VENDEDOR"
    r"|CCF\b|COO\b|ECF\b|VERS[AÃ]O|FAB:|S[EÉ]RIE|RAZ[AÃ]O\s+SOCIAL"
    r"|VALOR\s+PAGO|FORMA|QTDE|ITENS\b|www\.|http|R\$\s*$)",
    re.IGNORECASE,
)

_UNIDADES = r"UN|KG|CX|LT|PC|PCT|G|ML|L|M"

# Cabeçalho de item ECF (descrição numa linha, valores na seguinte).
# Ex.: "001 21259003 DESOD SANIT PINH-SANIFECT -35G"
# O número do item tolera trocas clássicas do OCR (0->o, 1->l/I, 5->s).
_ECF_DESC = re.compile(
    r"^(?P<item>[\dOoIlsS]{1,3})\s+(?P<cod>[0-9A-Za-z]{5,14})\s+(?P<desc>[A-Za-zÀ-ú+].{2,})$"
)

# Linha de valores do ECF. OCR costuma trocar "x" por "%", comer a
# quantidade ("3x" vira "sx") e colar lixo. O último preço da linha é o
# total do item e serve de prova para reconstruir a quantidade.
# Ex.: "1 x 1,09", "1% 1,09 717,00% 1,09", "sx2,29 717,00% 6,87"
_ECF_VALS = re.compile(
    r"^[a-zA-Z]?(?P<qtd>\d+(?:[.,]\d+)?)?\s*[xX%*]\s*"
    r"(?P<preco>\d{1,4}[.,]\d{2})"
    # Total no fim da linha, tolerando dígito de ST colado ("1,096").
    r"(?:.*\s(?P<total>\d{1,6}[.,]\d{2}))?.*$"
)

# Linha única com "x". Ex.: "FEIJAO 2 UN x 8,50 17,00", "BANANA 0,750 KG x 7,99"
_QTD_X_PRECO = re.compile(
    r"^(?:\d{1,7}\s+)?(?:\d{7,14}\s+)?"
    r"(?P<desc>.+?)\s+"
    r"(?P<qtd>\d+(?:[.,]\d+)?)\s*(?:" + _UNIDADES + r")?\s*"
    r"[xX*]\s*"
    r"(?P<preco>\d{1,4}[.,]\d{2})"
    r"(?:\s+(?P<total>\d{1,6}[.,]\d{2}))?"
)

# NFC-e sem "x". Ex.: "003277 PRODUTO 1,000 CX 15,10 15,10"
# OCR pode perder a vírgula da qtd ("30000") e do unitário ("2200").
_QTD_UN_PRECO_TOTAL = re.compile(
    r"^(?:\d{1,14}\s+)?"
    r"(?P<desc>[A-Za-zÀ-ú].*?)\s+"
    r"(?P<qtd>\d+(?:[.,]\d+)?)\s*(?:" + _UNIDADES + r")\b\.?\s+"
    r"(?P<preco>\d{1,6}(?:[.,]\d{2})?)\s+"
    r"(?P<total>\d{1,6}[.,]\d{2})\s*$",
    re.IGNORECASE,
)

# Dois preços no fim (qtd ilegível -> assume 1).
# Ex.: "SIMENTA CH UN 13,20 13,20"
_DESC_PRECO_TOTAL = re.compile(
    r"^(?:\d{1,14}\s+)?"
    r"(?P<desc>[A-Za-zÀ-ú].+?)\s+"
    r"(?P<preco>\d{1,4}[.,]\d{2})\s+"
    r"(?P<total>\d{1,6}[.,]\d{2})\s*$"
)

# Um preço no fim (qtd 1). Ex.: "002 PAO FRANCES 12,50"
_PRECO_FIM = re.compile(
    r"^(?:\d{1,7}\s+)?(?:\d{7,14}\s+)?"
    r"(?P<desc>[A-Za-zÀ-ú][A-Za-zÀ-ú0-9 .\-/%+]{2,})\s+"
    r"(?P<preco>\d{1,4}[.,]\d{2})\s*$"
)


def _numero(valor: str) -> float:
    valor = valor.strip()
    if "," in valor:
        return float(valor.replace(".", "").replace(",", "."))
    return float(valor)


def _preco_ocr(valor: str) -> float:
    """Preço que perdeu o separador no OCR: '2200' -> 22.00."""
    if "," in valor or "." in valor:
        return _numero(valor)
    inteiro = int(valor)
    return inteiro / 100 if len(valor) >= 3 else float(inteiro)


def _corrigir_quantidade(qtd: float, preco: float, total: float | None) -> int:
    """Usa o total da linha como prova quando o OCR corrompe a quantidade."""
    if total and preco > 0:
        proporcao = total / preco
        if 0 < proporcao <= 999:
            return max(1, round(proporcao))
    if qtd > 999:  # vírgula perdida ("1,000" -> "10000")
        return 1
    return max(1, round(qtd))


def _limpar_descricao(desc: str) -> str:
    # Limpeza mínima de propósito: o usuário revisa cada item na tela.
    # Remoções agressivas mutilam nomes legítimos ("PRODUTO B" -> "PRODUTO").
    desc = re.sub(r"[^\x20-\x7EÀ-ú]", " ", desc)  # lixo não imprimível do OCR
    return re.sub(r"\s{2,}", " ", desc).strip(" -.")


def _descricao_valida(desc: str) -> bool:
    return len(desc) >= 3 and len(re.findall(r"[A-Za-zÀ-ú]", desc)) >= 3


def parse_cupom(texto: str) -> list[dict]:
    """Extrai itens de compra do texto OCR de um cupom fiscal."""
    itens = []
    desc_pendente: str | None = None

    for linha in texto.splitlines():
        linha = linha.strip()
        if len(linha) < 4:
            continue

        # Valores do item ECF cuja descrição veio na linha anterior.
        if desc_pendente:
            match = _ECF_VALS.match(linha)
            if match:
                preco = _numero(match.group("preco"))
                qtd_bruta = _numero(match.group("qtd")) if match.group("qtd") else 1.0
                total = _numero(match.group("total")) if match.group("total") else None
                qtd = _corrigir_quantidade(qtd_bruta, preco, total)
                _adicionar(itens, desc_pendente, qtd, preco)
                desc_pendente = None
                continue
            desc_pendente = None  # linha seguinte não era de valores

        if _IGNORAR.search(linha):
            continue

        match = _QTD_X_PRECO.match(linha)
        if match:
            preco = _numero(match.group("preco"))
            total = _numero(match.group("total")) if match.group("total") else None
            qtd = _corrigir_quantidade(_numero(match.group("qtd")), preco, total)
            _adicionar(itens, match.group("desc"), qtd, preco)
            continue

        match = _QTD_UN_PRECO_TOTAL.match(linha)
        if match:
            preco = _preco_ocr(match.group("preco"))
            total = _numero(match.group("total"))
            qtd = _corrigir_quantidade(_numero(match.group("qtd")), preco, total)
            _adicionar(itens, match.group("desc"), qtd, preco)
            continue

        match = _DESC_PRECO_TOTAL.match(linha)
        if match:
            preco = _numero(match.group("preco"))
            total = _numero(match.group("total"))
            qtd = _corrigir_quantidade(1.0, preco, total)
            _adicionar(itens, match.group("desc"), qtd, preco)
            continue

        match = _PRECO_FIM.match(linha)
        if match:
            _adicionar(itens, match.group("desc"), 1, _numero(match.group("preco")))
            continue

        # Pode ser o cabeçalho de um item ECF em duas linhas.
        match = _ECF_DESC.match(linha)
        if match:
            desc_pendente = match.group("desc")

    return itens


def _adicionar(itens: list, desc: str, qtd: int, preco: float) -> None:
    descricao = _limpar_descricao(desc)
    if not _descricao_valida(descricao) or preco <= 0:
        return
    itens.append(
        {
            "descricao": descricao,
            "quantidade": qtd,
            "preco": round(preco, 2),
        }
    )
