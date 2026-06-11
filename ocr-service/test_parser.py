"""Testes do parser de cupom fiscal.

Por que importam: o parser é heurístico; estes testes fixam o contrato
de que linhas de item viram itens e linhas de rodapé/total nunca viram.
"""

from parser import parse_cupom

CUPOM_EXEMPLO = """
SUPERMERCADO EXEMPLO LTDA
CNPJ 12.345.678/0001-90
EXTRATO No 123456 CUPOM FISCAL ELETRONICO SAT
ITEM CODIGO DESCRICAO QTD UN VL UNIT VL TOTAL
001 7891234567890 ARROZ TIO JOAO 5KG 1 UN x 25,99 25,99
002 7890987654321 FEIJAO CARIOCA 1KG 2 UN x 8,50 17,00
003 LEITE INTEGRAL 1L 12,40
BANANA PRATA 0,750 KG x 7,99
TOTAL R$ 63,38
DINHEIRO 70,00
TROCO 6,62
OBRIGADO VOLTE SEMPRE
"""


def test_extrai_itens_com_quantidade_x_preco():
    itens = parse_cupom(CUPOM_EXEMPLO)
    descricoes = [item["descricao"] for item in itens]

    assert "ARROZ TIO JOAO 5KG" in descricoes
    assert "FEIJAO CARIOCA 1KG" in descricoes


def test_quantidade_e_preco_unitario_corretos():
    itens = parse_cupom(CUPOM_EXEMPLO)
    feijao = next(i for i in itens if "FEIJAO" in i["descricao"])

    assert feijao["quantidade"] == 2
    assert feijao["preco"] == 8.50


def test_linha_com_preco_no_fim_assume_quantidade_1():
    itens = parse_cupom(CUPOM_EXEMPLO)
    leite = next(i for i in itens if "LEITE" in i["descricao"])

    assert leite["quantidade"] == 1
    assert leite["preco"] == 12.40


def test_quantidade_fracionada_arredonda_com_minimo_1():
    itens = parse_cupom(CUPOM_EXEMPLO)
    banana = next(i for i in itens if "BANANA" in i["descricao"])

    assert banana["quantidade"] == 1  # 0,750 KG -> 1
    assert banana["preco"] == 7.99


def test_ignora_totais_pagamento_e_rodape():
    itens = parse_cupom(CUPOM_EXEMPLO)
    texto_itens = " ".join(i["descricao"] for i in itens).upper()

    assert "TOTAL" not in texto_itens
    assert "TROCO" not in texto_itens
    assert "DINHEIRO" not in texto_itens
    assert "CNPJ" not in texto_itens


def test_texto_vazio_retorna_lista_vazia():
    assert parse_cupom("") == []


# -----------------------------------------------------------------------------
# Layouts reais (trechos de OCR de notas verdadeiras, com erros típicos)
# -----------------------------------------------------------------------------

NFCE_REAL = """
RAZAO SOCIAL DA EMPRESA.
CNPJ: 00.000.000/000-99. IE: 00000000 00
003277 PRODUTO A 10000 cx 27,64 27,84
085273 PRODUTO B 30000 LT 2200 66,00
807194 PRODUTO C 1,000 CX 1510 15,10
046281 PRODUTO D 1,000 LT 30,00 30,00
QTDE. TOTAL DE ITENS 8
VALOR TOTAL R$ 138,74
Dinheiro 138,74
"""


def test_nfce_sem_x_extrai_itens():
    """NFC-e usa 'qtd UN unit total' sem o 'x' — precisa ser reconhecido."""
    itens = parse_cupom(NFCE_REAL)
    assert len(itens) == 4


def test_nfce_corrige_qtd_corrompida_pelo_total():
    """OCR leu '3,000' como '30000'; total/unitário prova que qtd = 3."""
    itens = parse_cupom(NFCE_REAL)
    produto_b = next(i for i in itens if "PRODUTO B" in i["descricao"])

    assert produto_b["preco"] == 22.00  # '2200' sem vírgula -> 22,00
    assert produto_b["quantidade"] == 3  # 66,00 / 22,00


def test_nfce_preco_sem_virgula_e_corrigido():
    itens = parse_cupom(NFCE_REAL)
    produto_c = next(i for i in itens if "PRODUTO C" in i["descricao"])

    assert produto_c["preco"] == 15.10
    assert produto_c["quantidade"] == 1


ECF_DUAS_LINHAS = """
COMERCIO DE ALIMENTOS
CUPOM FISCAL
001 21259003 DESOD SANIT PINH-SANIFECT -35G
1% 1,09 717,00% 1,09
002 57192502 QUEIJO MUSSARELA-GIROLANDA -KG
0,3% 17,49 717,00% 5,24
003 57224500 BATATA PALHA SLI-MICOS -706
sx2,29 717,00% 6,87
TOTAL R$ 21,71
Dinheiro 21,71
"""


def test_ecf_item_em_duas_linhas():
    """Cupom ECF tem descrição numa linha e valores na seguinte."""
    itens = parse_cupom(ECF_DUAS_LINHAS)
    descricoes = " ".join(i["descricao"] for i in itens)

    assert "DESOD SANIT" in descricoes
    assert "QUEIJO MUSSARELA" in descricoes
    assert "BATATA PALHA" in descricoes


def test_ecf_x_lido_como_porcento():
    """OCR troca 'x' por '%' — '1% 1,09' significa '1 x 1,09'."""
    itens = parse_cupom(ECF_DUAS_LINHAS)
    desod = next(i for i in itens if "DESOD" in i["descricao"])

    assert desod["quantidade"] == 1
    assert desod["preco"] == 1.09


def test_nota_com_dois_precos_no_fim_assume_qtd_1():
    """Quando a qtd é ilegível, dois preços iguais no fim => qtd 1."""
    itens = parse_cupom("SIMENTA CH A UN A 13,20 13,20")

    assert len(itens) == 1
    assert itens[0]["quantidade"] == 1
    assert itens[0]["preco"] == 13.20
    # Limpeza é mínima de propósito (usuário revisa na tela); o nome só
    # não pode ser mutilado.
    assert itens[0]["descricao"].startswith("SIMENTA CH")


def test_descricao_com_letra_final_nao_e_mutilada():
    """'PRODUTO B' tem que continuar 'PRODUTO B' — limpeza agressiva já quebrou isso."""
    itens = parse_cupom("085273 PRODUTO B 30000 LT 2200 66,00")

    assert itens[0]["descricao"] == "PRODUTO B"
