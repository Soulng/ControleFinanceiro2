"""
API Flask para buscar dados de FIIs em tempo real via Yahoo Finance.
Serve como backend para a página de investimentos do Finance Easy.
"""

from flask import Flask, jsonify
from flask_cors import CORS
import yfinance as yf
import time

app = Flask(__name__)
CORS(app)

# Lista curada de FIIs acessíveis ao grande público
# Inclui FIIs baratos (abaixo de R$150) com boa liquidez e histórico
FIIS_LISTA = [
    # FIIs de Papel (CRI/LCI) - geralmente mais baratos
    {'ticker': 'MXRF11', 'tipo': 'Papel', 'descricao': 'FII mais popular da B3. Investe em recebíveis imobiliários (CRIs) com portfólio altamente diversificado.'},
    {'ticker': 'VGHF11', 'tipo': 'Híbrido', 'descricao': 'Estratégia flexível que investe em cotas de outros FIIs e CRIs, proporcionando diversificação automática.'},
    {'ticker': 'RBRF11', 'tipo': 'Fundo de Fundos', 'descricao': 'Fundo de fundos que investe em cotas de outros FIIs, ideal para diversificação com baixo capital.'},
    {'ticker': 'HCTR11', 'tipo': 'Papel', 'descricao': 'Fundo de recebíveis com foco em CRIs de alto rendimento, voltado para renda passiva consistente.'},
    {'ticker': 'GGRC11', 'tipo': 'Tijolo', 'descricao': 'Fundo de galpões logísticos com contratos de longo prazo, bom para renda estável.'},
    {'ticker': 'KNIP11', 'tipo': 'Papel', 'descricao': 'Kinea Índice de Preços. Investe em CRIs indexados ao IPCA, proteção contra inflação.'},
    {'ticker': 'KNCR11', 'tipo': 'Papel', 'descricao': 'Kinea Rendimentos. Fundo de CRIs com indexação ao CDI, ideal para cenário de juros altos.'},
    {'ticker': 'IRDM11', 'tipo': 'Papel', 'descricao': 'Iridium Recebíveis. Portfólio diversificado de CRIs com gestão ativa e bom histórico de dividendos.'},
    {'ticker': 'XPLG11', 'tipo': 'Tijolo', 'descricao': 'XP Log. Fundo de galpões logísticos de alta qualidade com inquilinos de grande porte.'},
    {'ticker': 'BTLG11', 'tipo': 'Tijolo', 'descricao': 'BTG Pactual Logística. Portfólio de galpões logísticos premium com contratos longos.'},
    {'ticker': 'VISC11', 'tipo': 'Tijolo', 'descricao': 'Vinci Shopping Centers. Fundo de shoppings com boa localização e alta frequência de visitantes.'},
    {'ticker': 'XPML11', 'tipo': 'Tijolo', 'descricao': 'XP Malls. Portfólio de shoppings diversificado com foco em regiões de alto poder aquisitivo.'},
    {'ticker': 'HGLG11', 'tipo': 'Tijolo', 'descricao': 'CSHG Logística. Um dos maiores fundos de logística do Brasil, com gestão experiente e portfólio premium.'},
    {'ticker': 'RECR11', 'tipo': 'Papel', 'descricao': 'REC Recebíveis. Portfólio diversificado em CRIs com indexação ao IPCA. Muito recomendado por analistas.'},
    {'ticker': 'BCFF11', 'tipo': 'Fundo de Fundos', 'descricao': 'BTG Pactual Fundo de Fundos. Diversificação automática em dezenas de FIIs com gestão profissional.'},
]

# Cache simples para evitar muitas requisições
_cache = {}
_cache_time = {}
CACHE_DURATION = 300  # 5 minutos

def get_fii_data(ticker):
    """Busca dados de um FII via Yahoo Finance com cache."""
    now = time.time()
    if ticker in _cache and (now - _cache_time.get(ticker, 0)) < CACHE_DURATION:
        return _cache[ticker]

    try:
        yf_ticker = yf.Ticker(f"{ticker}.SA")
        info = yf_ticker.info

        preco = info.get('currentPrice') or info.get('regularMarketPrice')
        dy_raw = info.get('dividendYield')
        dy = round(dy_raw, 4) if dy_raw else None
        nome = info.get('longName') or info.get('shortName') or ticker

        # Limpar nome longo
        nome = nome.replace('Fundo De Investimento Imobiliaro', '').replace(
            'Fundo De Investimento Imobiliario', '').replace(
            'Fundo Investimento Imobiliario', '').replace(
            'Fundo de Investimento Imobiliário', '').replace(
            'FII', '').replace('- FII', '').strip()
        if nome.endswith(' -'):
            nome = nome[:-2].strip()

        data = {
            'ticker': ticker,
            'nome': nome,
            'preco': round(preco, 2) if preco else None,
            'dividendYield': dy,
            'variacao': info.get('regularMarketChangePercent'),
            'volume': info.get('regularMarketVolume'),
            'pvp': info.get('priceToBook'),
            'erro': False
        }

        _cache[ticker] = data
        _cache_time[ticker] = now
        return data

    except Exception as e:
        return {'ticker': ticker, 'erro': True, 'mensagem': str(e)}


@app.route('/api/fiis', methods=['GET'])
def listar_fiis():
    """Retorna lista de FIIs com dados em tempo real."""
    resultado = []
    for fii_info in FIIS_LISTA:
        ticker = fii_info['ticker']
        dados = get_fii_data(ticker)
        if not dados.get('erro'):
            dados['tipo'] = fii_info['tipo']
            dados['descricao'] = fii_info['descricao']
            resultado.append(dados)

    # Ordenar por preço (mais baratos primeiro)
    resultado.sort(key=lambda x: x.get('preco') or 9999)

    return jsonify({
        'success': True,
        'total': len(resultado),
        'dados': resultado,
        'timestamp': int(time.time())
    })


@app.route('/api/fiis/baratos', methods=['GET'])
def fiis_baratos():
    """Retorna apenas FIIs com cotas abaixo de R$50 (mais acessíveis)."""
    resultado = []
    for fii_info in FIIS_LISTA:
        ticker = fii_info['ticker']
        dados = get_fii_data(ticker)
        if not dados.get('erro') and dados.get('preco') and dados['preco'] <= 50:
            dados['tipo'] = fii_info['tipo']
            dados['descricao'] = fii_info['descricao']
            resultado.append(dados)

    resultado.sort(key=lambda x: x.get('preco') or 9999)

    return jsonify({
        'success': True,
        'total': len(resultado),
        'dados': resultado,
        'timestamp': int(time.time())
    })


@app.route('/api/fiis/status', methods=['GET'])
def status():
    return jsonify({'status': 'ok', 'timestamp': int(time.time())})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5050, debug=False)
