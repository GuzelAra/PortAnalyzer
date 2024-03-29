#!/var/www/u717574/data/venv/bin/python3.6
# -*- coding: UTF-8 -*-

import os
import cgitb
import sys
import gate_api
from gate_api.exceptions import ApiException, GateApiException
from datetime import datetime, timedelta
import pymysql.cursors

os.environ['OPENBLAS_NUM_THREADS'] = '1'
os.environ["PYTHONIOENCODING"] = "utf-8"

cgitb.enable()

print ("Content-Type: text/plain;charset=utf-8")
print ()

print ("SCRIPT STARTED!")
print (sys.version)


# Configure APIv4 key authorization
configuration = gate_api.Configuration(
    host="https://api.gateio.ws/api/v4",
    key="42268e4f91e2c12ae397c0b2c6262930",  # данные Гузель
    secret="574e06dcbe72faec3ef6b890b430e726540f0ac37e13f70d9842d13e03d9a365" # данные Гузель
)

# API
api_client = gate_api.ApiClient(configuration)
api_instance = gate_api.SpotApi(api_client)
api_instance_wallet = gate_api.WalletApi(api_client)

# получаем из БД из таблицы coin_sectors данные пo соответствию монет секторам
# получаем из БД из таблицы blocked_coins список монет, которые не надо учитывать в подсчетах
# получаем из БД из таблицы average_price_coins список монет и их среднюю цену покупки
SectorsArray = []
BlockedCoinsArray = []
AveragePriceCoinsArray = []
WalletArray = []

def read_db():
    # Параметры подключения к БД
    connection = pymysql.connect(host='91.236.136.57',
                                 user='u717574_guzel',
                                 password='qwerty123',
                                 database='u717574_crypto',
                                 charset='utf8mb4',
                                 cursorclass=pymysql.cursors.DictCursor)
    table_name = 'coins_sectors'

    with connection:
        with connection.cursor() as cursor:
            # получаем сектора из БД (только уникальные поля to столбцу Coin, т.к. есть дубли тк много тегов to 1 монете)
            sectorsDB = "SELECT DISTINCT(`Symbol`), `Sector` FROM %s" % (table_name)
            cursor.execute(sectorsDB)
            data_table = cursor.fetchall()
            # print()
            #print(data_table)


    # добавляем в массив уникальные соответствия секторов монетам
    for row in data_table:
        SectorsArray.append([row['Symbol'], row['Sector']])
    # print (row)

    # print()
    print(SectorsArray)

read_db()


# Массив с монетами, данные to которым не следует обрабатывать в некоторых моментах
ArrStopCoin = ['USDT', 'POINT']
# Объявляем массив для данных портфеля
ArrPortfolio = []
# Объявляем массив для стейблкоинов (чтобы отображать их как кошелек, отдельно от инвестиций)
ArrPortfolioStableCoins = []

# Получаем инфу to кол-ву указанной монеты в портфеле
def portfolio():
    global ArrPortfolio
    try:
        # List spot accounts
        # api_response = api_instance.list_spot_accounts(currency=currency)
        api_response = api_instance.list_spot_accounts()
        # print('API ersponse: ', api_response)

        # Добавляем в массив данные to монетам
        for i in range(len(api_response)):
            # если монета не в списке блокировки, то добавляем ее в массив ArrPortfolio
            if (api_response[i].currency != 'USDT'):
                itogo = float(api_response[i].available) + float(api_response[i].locked)
                ArrPortfolio.append([api_response[i].currency, float(api_response[i].available), float(api_response[i].locked), itogo])
            else:
                itogo = float(api_response[i].available) + float(api_response[i].locked)
                ArrPortfolioStableCoins.append(
                    [api_response[i].currency, float(api_response[i].available), float(api_response[i].locked),
                     itogo])

        print('Get portfolio - OK')
        print(str(ArrPortfolio))

    except GateApiException as ex:
        print("Gate api exception, label: %s, message: %s\n" % (ex.label, ex.message))
    except ApiException as e:
        print("Exception when calling SpotApi->list_spot_accounts: %s\n" % e)


portfolio()




# Получаем текущее время
dt = datetime.now()
# getting the timestamp
ts = int(datetime.timestamp(dt))  # текущая дата
# вычетаем 30 дней из текущей даты, чтобы получить дату 30-тидневной давности
d1 = datetime.today() - timedelta(days=30)
ds1 = int(datetime.timestamp(d1))  # дата -30 дней

# автоматизируем то, что выше
arrD = []
arrDS = []

# Объявляем массив для истории сделок за последний год
ArrDealHistory = []

# Получаем историю сделок
def deal_history():
    # автоматизируем то, что выше
    for i in range(1, 25):
        x = i * 30
        arrD.append(datetime.today() - timedelta(days=x))
        arrDS.append(int(datetime.timestamp(datetime.today() - timedelta(days=x))))


    limit = 100  # int | Maximum number of records to be returned in a single list (optional) (default to 100)
    page = 1  # int | Page number (optional) (default to 1)
    order_id = '12345'  # str | Filter trades with specified order ID. `currency_pair` is also required if this field is present (optional)
    account = 'spot'  # str | Specify operation account. Default to spot and margin account if not specified. Set to `cross_margin` to operate against margin account.  Portfolio margin account must set to `cross_margin` only (optional)
    _from = ds1  # 1671909208 # int | Start timestamp of the query (optional)
    to = ts  # int | Time range ending, default to current time (optional)

    # Цикл по последним 12 месяцам с текущего и до 2х лет назад
    for month in range(1, 6):
        try:
            # List personal trading history
            api_response = api_instance.list_my_trades(account=account, limit=limit, page=page, _from=arrDS[month],
                                                       to=arrDS[month - 1])
            # Добавляем в массив данные to монетам
            for a in range(len(api_response)):
                deal_timestamp = int(api_response[a].create_time)  # присваиваем переменной значение timestamp сделки
                deal_date = datetime.fromtimestamp(deal_timestamp)  # конвертим timestamp в читаемый формат даты
                deal_date_format = deal_date.strftime("%d.%m.%Y")  # задаем нужный формат вывода даты
                deal_time_format = deal_date.strftime("%H:%M:%S")  # задаем нужный формат вывода времени
                ArrDealHistory.append(
                    [api_response[a].create_time, deal_date_format, deal_time_format, api_response[a].currency_pair,
                     float(api_response[a].amount), float(api_response[a].price), float(api_response[a].fee),
                     api_response[a].fee_currency, api_response[a].side, api_response[a].order_id])
            print('Get history deals from ', arrD[month], ' to ', arrD[month - 1], ' - OK')

        except GateApiException as ex:
            print("Gate api exception, label: %s, message: %s\n" % (ex.label, ex.message))
        except ApiException as e:
            print("Exception when calling SpotApi->list_my_trades: %s\n" % e)


    # История операций с -1 мес. to текущую дату
    try:
        api_response = api_instance.list_my_trades(account=account, limit=limit, page=page, _from=ds1, to=ts)

        # print('-----------')
        # print(api_response)
        # print('-----------')

        # Добавляем в массив данные to монетам
        for a in range(len(api_response)):
            deal_timestamp = int(api_response[a].create_time)  # присваиваем переменной значение timestamp сделки
            deal_date = datetime.fromtimestamp(deal_timestamp)  # конвертим timestamp в читаемый формат даты
            deal_date_format = deal_date.strftime("%d.%m.%Y")  # задаем нужный формат вывода даты
            deal_time_format = deal_date.strftime("%H:%M:%S")  # задаем нужный формат вывода времени
            ArrDealHistory.append(
                [api_response[a].create_time, deal_date_format, deal_time_format, api_response[a].currency_pair,
                 float(api_response[a].amount), float(api_response[a].price), float(api_response[a].fee),
                 api_response[a].fee_currency, api_response[a].side, api_response[a].order_id])
        print('Get history deals from ', d1, ' to ', dt, ' - OK')
        print(str(ArrDealHistory))

    except GateApiException as ex:
        print("Gate api exception, label: %s, message: %s\n" % (ex.label, ex.message))
    except ApiException as e:
        print("Exception when calling SpotApi->list_my_trades: %s\n" % e)

deal_history()


# Находим среднюю цену покупки каждой монеты
def AveragePrice():
    for x in range(len(ArrPortfolio)):
        CoinAverage = 0
        CoinCount = 0
        CoinAveragePrice = 0
        for y in range(len(ArrDealHistory)):
            # если монета в портфеле = монете в истории и сделка в истории = "покупка"
            a = ArrPortfolio[x][0]  # монета в портфеле
            b = (ArrDealHistory[y][3])[:-5]  # монета в истории сделок
            c = ArrDealHistory[y][8]  # тип сделки
            if (a == b and c == 'buy'):
                CoinAverage = CoinAverage + (float(ArrDealHistory[y][4]) * float(ArrDealHistory[y][5]))
                CoinCount = CoinCount + float(ArrDealHistory[y][4])

        # Добавляем в массив среднюю цену покупки по каждой монете
        if CoinCount != 0:
            CoinAveragePrice = CoinAverage / CoinCount
        else:
            CoinAveragePrice = 0

        # Добавляем в массив портфеля ArrPortfolio значения средней цены покупки CoinAveragePrice
        ArrPortfolio[x].insert(4, CoinAveragePrice)
    print(ArrPortfolio)

AveragePrice()



# получаем инфу по кол-ву открытых ордеров по монетам
ArrOrdersСount = []


def order_count():
    page = 1  # int | Page number (optional) (default to 1)
    limit = 100  # int | Maximum number of records returned in one page in each currency pair (optional) (default to 100)

    try:
        # List all open orders
        api_response2 = api_instance.list_all_open_orders(page=page, limit=limit)
        # print(api_response2)
        print('Count money with open orders: ' + str(len(api_response2)))

        # Добавляем в массив данные по монетам
        for i in range(len(api_response2)):
            # for j in range (len(api_response2[i].orders)):
            currency_pair = api_response2[i].currency_pair[:-5]
            total = api_response2[i].total
            # if total == '': total = 0
            ArrOrdersСount.append([currency_pair, total])  # , api_response2[i].orders[j].side
        print('Get count of opened orders - OK')
        # print(str(ArrOrdersСount))

        # Дополняем массив ArrPortfolio данными по количеству открытых ордеров
        for a in range(len(ArrPortfolio)):
            a_currency = ArrPortfolio[a][0]
            for b in range(len(ArrOrdersСount)):
                b_currency = ArrOrdersСount[b][0]
                if a_currency == b_currency:
                    # Добавляем в массив портфеля ArrPortfolio кол-во открытых ордеров
                    ArrPortfolio[a].insert(5, ArrOrdersСount[b][1])

            # Добавил условие: если монета = USDT или POINT, либо кол-во монеты меньше 0,001,
            # то кол-во ордеров по данной монете устанавливается = 0
            if (a_currency in ArrStopCoin):  # or float(ArrPortfolio[a][3]) < 0.001
                ArrPortfolio[a].insert(5, 0)
            # Если по монете еще не выставлены ордера (то в списке ордеров монеты не будет), поэтому добавляем кол-во ордеров = 0,
            # чтобы размер списка по монете соответствовал размеру списков остальных монет
            if len(ArrPortfolio[a]) < 6:
                ArrPortfolio[a].insert(5, 0)

        print('Добавили кол-во открытых ордеров:')
        print(ArrPortfolio)

    except GateApiException as ex:
        print("Gate api exception, label: %s, message: %s\n" % (ex.label, ex.message))
    except ApiException as e:
        print("Exception when calling SpotApi->list_all_open_orders: %s\n" % e)


order_count()






# API4 Получаем текущие цены монет, которые есть в портфеле (в массиве ArrPortfolio)
ArrCurrentPrice2 = []
def currency_current_price2():
    # инфа по ценам всех пар (использовать это вместо API2 в def currency_current_price())
    # base_volume - объем торгов в базовой валюте
    # change_percentage - процент изменения цены
    # currency_pair - торговая пара
    # high_24h - макс цена за 24ч
    # highest_bid - максимальная цена, по которой покупатель согласен купить
    # last - текущая цена
    # low_24h - мин цена за 24ч
    # lowest_ask - наименьшая цена, по которой продавец согласен продать
    # quote_volume - объем торгов

    # currency_pair = 'BTC_USDT' # str | Currency pair (optional)
    timezone = 'utc0'  # str | Timezone (optional)

    print('Get data about current perice coins (API 4) - OK')
    try:
        # Retrieve ticker information
        # api_response = api_instance.list_tickers(currency_pair=currency_pair, timezone=timezone)
        api_response = api_instance.list_tickers(timezone=timezone)
    # print(api_response)
    # print(api_response[0].currency_pair, api_response[0].high_24h)
    except GateApiException as ex:
        print("Gate api exception, label: %s, message: %s\n" % (ex.label, ex.message))
    except ApiException as e:
        print("Exception when calling SpotApi->list_tickers: %s\n" % e)

    # проходимся по монетам в портфеле, чтобы сопоставить их с полученным выше массивом данных
    for i in range(len(ArrPortfolio)):
        currency = ArrPortfolio[i][0]
        # print(currency)
        currency_pair = currency + '_usdt'

        # проходимся по монетам, по которым получили данные о ценах за день
        for j in range(len(api_response)):
            if currency_pair.upper() == api_response[j].currency_pair.upper():
                # Добавляем текущие цены и остальные данные в массив
                ArrCurrentPrice2.append(
                    [currency, api_response[j].last, api_response[j].low_24h, api_response[j].high_24h,
                     api_response[j].change_percentage, api_response[j].quote_volume])

    # print('ArrCurrentPrice2')
    # print(ArrCurrentPrice2)

    # Дополняем массив ArrPortfolio данными текущих цен монет
    for a in range(len(ArrPortfolio)):
        for b in range(len(ArrCurrentPrice2)):
            a_currency = ArrPortfolio[a][0]
            b_currency = ArrCurrentPrice2[b][0]
            if a_currency == b_currency:
                # Добавляем в массив портфеля ArrPortfolio кол-во открытых ордеров
                ArrPortfolio[a].insert(6, float(ArrCurrentPrice2[b][1]))
                ArrPortfolio[a].insert(7, float(ArrCurrentPrice2[b][2]))
                ArrPortfolio[a].insert(8, float(ArrCurrentPrice2[b][3]))
                ArrPortfolio[a].insert(9, float(ArrCurrentPrice2[b][4]))
                ArrPortfolio[a].insert(10, float(ArrCurrentPrice2[b][5]))
                break

print('Добавили текущую цену монеты:')
print(ArrPortfolio)
# print()

currency_current_price2()


# инфа по открытым ордерам (цикл по каждой монете в портфеле)
ArrOrderOpened = []


def order_opened():
    # Через цикл надо пройтись по всем монетам в портфеле и вытащить открытые ордера
    for ap in range(len(ArrPortfolio)):
        if ArrPortfolio[ap][0] in ArrStopCoin: continue  # не обрабатывать пары USDT и POINT
        para = ArrPortfolio[ap][0] + '_USDT'
        # print(para)

        currency_pair = para  # str | Retrieve results with specified currency pair. It is required for open orders, but optional for finished ones.
        status = 'open'  # str | List orders based on status  `open` - order is waiting to be filled `finished` - order has been filled or cancelled
        page = 1  # int | Page number (optional) (default to 1)
        limit = 100  # int | Maximum number of records to be returned. If `status` is `open`, maximum of `limit` is 100 (optional) (default to 100)
        account = 'spot'  # str | Specify operation account. Default to spot and margin account if not specified. Set to `cross_margin` to operate against margin account.  Portfolio margin account must set to `cross_margin` only (optional)
        # _from = ds # int | Start timestamp of the query (optional)
        # to = ts # int | Time range ending, default to current time (optional)
        # side = 'sell' # str | All bids or asks. Both included if not specified (optional)

        try:
            # List orders
            api_response3 = api_instance.list_orders(currency_pair, status, page=page, limit=limit, account=account)

            # Добавляем в массив данные по монетам
            for a in range(len(api_response3)):
                deal_timestamp = int(api_response3[a].create_time)  # присваиваем переменной значение timestamp сделки
                deal_date = datetime.fromtimestamp(deal_timestamp)  # конвертим timestamp в читаемый формат даты
                deal_date_format = deal_date.strftime("%d.%m.%Y")  # задаем нужный формат вывода даты
                deal_time_format = deal_date.strftime("%H:%M:%S")  # задаем нужный формат вывода времени
                ArrOrderOpened.append(
                    [api_response3[a].create_time, deal_date_format, deal_time_format, api_response3[a].currency_pair,
                     float(api_response3[a].amount),
                     float(api_response3[a].price), float(api_response3[a].fee), api_response3[a].fee_currency,
                     api_response3[a].side, api_response3[a].status, api_response3[a].type, api_response3[a].id])

            # print(ArrOrderOpened)

        except GateApiException as ex:
            print("Gate api exception, label: %s, message: %s\n" % (ex.label, ex.message))
        except ApiException as e:
            print("Exception when calling SpotApi->list_orders: %s\n" % e)

order_opened()


def save2dbPortfolio():
    # Параметры подключения к БД
    db = pymysql.connect(host='91.236.136.57',
                         user='u717574_guzel',
                         password='qwerty123',
                         database='u717574_crypto',
                         charset='utf8mb4',
                         cursorclass=pymysql.cursors.DictCursor)
    table_name = 'portfolio'
    table_name2 = 'portfolio_sectors'
    table_name3 = 'wallet'

    cursor = db.cursor()

    delete = "DELETE FROM %s" % (table_name)
    delete2 = "DELETE FROM %s" % (table_name2)
    delete3 = "DELETE FROM %s" % (table_name3)
    cursor.execute(delete)
    cursor.execute(delete2)
    cursor.execute(delete3)

    PortfolioAdd = []

    # print ("ArrPortfolio: ", ArrPortfolio)

    sumCurrentPrice = 0
    for subArrPortfolio in ArrPortfolio:
        currency = subArrPortfolio[0]
        total = subArrPortfolio[3]
        if currency not in ArrStopCoin:
            currentPrice = subArrPortfolio[6]
        else:
            currentPrice = 0
        totalCurrentPrice = float(currentPrice) * float(total)
        # Считаем текущую стоимость всех монет портфеля (чтобы далее посчитать долю каждой монеты)
        sumCurrentPrice = sumCurrentPrice + float(totalCurrentPrice)

    # i = 2
    # print ('SectorsArray: ', SectorsArray)
    for subArrPortfolio in ArrPortfolio:
        currency = subArrPortfolio[0]

        # Добавляем сектора по каждой монете в массив ArrPortfolio
        for subSectorsArray in SectorsArray:
            coinSect = subSectorsArray[0]
            if coinSect == currency:
                sector = subSectorsArray[1]
                break
            else:
                sector = 'None'

        available = subArrPortfolio[1]
        blocked = subArrPortfolio[2]
        total = subArrPortfolio[3]
        averagePrice = subArrPortfolio[4]
        if currency not in ArrStopCoin:
            ordersCount = subArrPortfolio[5]
            currentPrice = subArrPortfolio[6]
            min24h = subArrPortfolio[7]
            max24h = subArrPortfolio[8]
            changeDay = subArrPortfolio[9]
            volume = subArrPortfolio[10]
            # прибыль/убыток
            profitLoss = (float(currentPrice) - float(averagePrice)) * float(total)
        else:
            ordersCount = 0
            currentPrice = 0
            min24h = 'none'
            max24h = 'none'
            changeDay = 0
            volume = 0
            profitLoss = 0
        # общая стоимость монет (по закупочной цене)
        totalAveragePrice = float(averagePrice) * float(total)
        # общая стоимость монет (по текущей цене)
        totalCurrentPrice = float(currentPrice) * float(total)

        # Если монета не в списке блока, то считаем ее долю
        if currency not in BlockedCoinsArray:
            # Доля(%) каждой монеты в портфеле
            percentOfPortfolio = float(totalCurrentPrice / sumCurrentPrice * 100)
        else:
            percentOfPortfolio = 0

        # Собираем список всех данных to монетам в портфеле для добавления в БД
        PortfolioAdd.append(
            [currency, sector, available, blocked, total, averagePrice, currentPrice, min24h, max24h, changeDay,
             profitLoss, volume, totalAveragePrice, totalCurrentPrice, percentOfPortfolio, ordersCount])
    # print(PortfolioAdd)

    # Считаем долю каждого сектора пo портфелю
    # Получаем список уникальных секторов пo портфелю
    uniqueSectors = []
    for subPortfolioAdd in PortfolioAdd:
        sector = subPortfolioAdd[1]
        totalCurrentPrice = subPortfolioAdd[13]
        if sector not in uniqueSectors:
            uniqueSectors.append(sector)
    # print('Portfolio sectors: ', uniqueSectors)

    # Далее в цикле to секторам считаем totalCurrentPrice to каждому отдельному сектору
    uniqueSectorsPercent = []  # массив с секторами и их долями
    for x in range(len(uniqueSectors)):
        sectorUniq = uniqueSectors[x]
        totalCurrentPriceSector = 0
        for y in range(len(PortfolioAdd)):
            sectorPortfolio = PortfolioAdd[y][1]
            # Если совпадение to сектору, то считаем сумму totalCurrentPrice для этого сектора
            if sectorUniq == sectorPortfolio:
                totalCurrentPriceSector = totalCurrentPriceSector + PortfolioAdd[y][13]

        # На основе суммы totalCurrentPriceSector и общей суммы всех монет пo портфелю sumCurrentPrice
        # считаем долю каждого сектора
        percentSector = float(totalCurrentPriceSector / sumCurrentPrice * 100)

        # Добавляем данные to доле сектора в итоговый массив (который будет добавлен в БД)
        # PortfolioAdd[x].insert(16, percentSector)

        # Можно создать отдельную таблицу и добавить сектор + доля в нее
        uniqueSectorsPercent.append([sectorUniq, percentSector, totalCurrentPriceSector])
    # print(PortfolioAdd[x])
    # print(len(PortfolioAdd[x]))
    print(uniqueSectorsPercent)
    # print(PortfolioAdd)

    for x in range(len(PortfolioAdd)):
        sectorPortfolio = PortfolioAdd[x][1]
        for y in range(len(uniqueSectorsPercent)):
            sectorUniq = uniqueSectorsPercent[y][0]
            percentUniq = uniqueSectorsPercent[y][1]
            totalCurrenPriceUniq = uniqueSectorsPercent[y][2]
            if sectorPortfolio == sectorUniq:
                PortfolioAdd[x].insert(15, totalCurrenPriceUniq)
                PortfolioAdd[x].insert(16, percentUniq)

    # print(PortfolioAdd)
    # sys.exit()

   # try:
    # создаём переменную с запросом
    insert = """INSERT INTO """ + table_name + """ (Symbol, Sector, Available, Blocked, Total, AveragePrice, CurrentPrice, Min24h, Max24h, Change24h, ProfitLoss, Volume, TotalAveragePrice, TotalCurrentPrice, Percent, СurrentPriceSector, PercentSector, OpenOrdersCount)
    values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s);"""
    # выполняем запрос
    print("Add data to table Portfolio - SUCCESS")
    print(PortfolioAdd)
    cursor.executemany(insert, PortfolioAdd)

    # создаём переменную с запросом
    insert = """INSERT INTO """ + table_name2 + """ (Sector, PercentSector, СurrentPriceSector)
            values (%s, %s, %s);"""
    # выполняем запрос
    print("Add data to table Portfolio_Sectors - SUCCESS")
    cursor.executemany(insert, uniqueSectorsPercent)

    # создаём переменную с запросом
    insert = """INSERT INTO """ + table_name3 + """ (Symbol, Available, Blocked, Total)
            values (%s, %s, %s, %s);"""
    # выполняем запрос
    print("Add data to table Wallet - SUCCESS")
    cursor.executemany(insert, ArrPortfolioStableCoins)

    db.commit()  # Чтобы сохранить данные в БД

    # закрываем курсор
    cursor.close()
    # закрываем соединение (если больше не оно не нужно)
    db.close()

    # except:
     #   print('Error add to Portfolio or Portfolio_Sectors')


save2dbPortfolio()



# т.к. изначально с gate.io нельзя получить id криптомонет,
# мы их дополнительно добавляем в таблицу portfolio из заранее подготовленной таблицы coins
# (где есть соответствия названий/символов монет их id)
def updateSymbol_id_in_portfolio():
    # Параметры подключения к БД
    db = pymysql.connect(host='91.236.136.57',
                         user='u717574_guzel',
                         password='qwerty123',
                         database='u717574_crypto',
                         charset='utf8mb4',
                         cursorclass=pymysql.cursors.DictCursor)
    # table_name = 'portfolio'
    # table_name2 = 'coins'
    cursor = db.cursor()

    try:
        # создаём переменную с запросом
        update = "UPDATE portfolio p, coins c SET p.Symbol_id = c.id WHERE p.Symbol = c.Symbol;"
        # выполняем запрос
        print("Update Symbol_id in table Portfolio - SUCCESS")
        cursor.execute(update)

        db.commit()  # Чтобы сохранить данные в БД

        # закрываем курсор
        cursor.close()
        # закрываем соединение (если больше не оно не нужно)
        db.close()

    except:
        print('Error add to Portfolio or Portfolio_Sectors')


updateSymbol_id_in_portfolio()





def save2dbDealsHistory():
    # Параметры подключения к БД
    db = pymysql.connect(host='91.236.136.57',
                         user='u717574_guzel',
                         password='qwerty123',
                         database='u717574_crypto',
                         charset='utf8mb4',
                         cursorclass=pymysql.cursors.DictCursor)

    table_name = 'deals_history'
    # Подключение к БД
    cursor = db.cursor()

    dealsHistoryArr = []
    i = 2
    for subArrDealHistory in ArrDealHistory:
        timeStamp = subArrDealHistory[0]
        data = subArrDealHistory[1]
        data = datetime.strptime(data, "%d.%m.%Y").strftime("%Y-%m-%d")
        vremya = subArrDealHistory[2]
        currency = subArrDealHistory[3]
        count = subArrDealHistory[4]
        price = subArrDealHistory[5]
        komiss = subArrDealHistory[6]
        komissCoin = subArrDealHistory[7]
        komissUSDT = 0
        dealPrice = round(float(count * price), 2)  # сумма сделки
        dealType = subArrDealHistory[8]
        dealID = subArrDealHistory[9]
        if dealType == 'buy':
            # при покупке монеты комиссия считается в монете, котору мы покупаем
            # чтобы видеть размер этой комиссии в USDT умножаем размер комиссии на текущую цену этой монеты в USDT
            komissUSDT = round(float(komiss * price), 2)
        else:
            # если же сделка на продажу монеты в USDT (sell), то комиссия и так будет сниматься в USDT
            komissUSDT = komiss

        dealsHistoryArr.append(
            [timeStamp, data, vremya, currency, count, price, komiss, komissCoin, komissUSDT, dealPrice, dealType,
             dealID])

    # удаляем из БД предыдущие данные по портфелю
    # cursor.execute("""DELETE FROM""" + table_name)
    delete = "DELETE FROM %s" % (table_name)
    cursor.execute(delete)

    try:
        # создаём переменную с запросом
        insert = """INSERT INTO """ + table_name + """ (timestamp, date, time, CurrencyPair, Count, Price, Fee, FeeCoin, FeeUSDT, DealPrice, DealType, DealID)
		values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s);"""
        # выполняем запрос
        print("Add data to table deals_history -  SUCCESS")
        cursor.executemany(insert, dealsHistoryArr)

        db.commit()  # Чтобы сохранить данные в БД

        # закрываем курсор
        cursor.close()
        # закрываем соединение (если больше не оно не нужно)
        db.close()

    # добавим обработку ошибок
    except MySQLdb.Error as e:
        print("MySQL Error [%d]: %s" % (e.args[0], e.args[1]))


save2dbDealsHistory()




def save2dbOrders():
    # Параметры подключения к БД
    db = pymysql.connect(host='91.236.136.57',
                         user='u717574_guzel',
                         password='qwerty123',
                         database='u717574_crypto',
                         charset='utf8mb4',
                         cursorclass=pymysql.cursors.DictCursor)
    table_name = 'open_orders'

    cursor = db.cursor()

    OrdersArr = []

    i = 2
    for subArrOrderOpened in ArrOrderOpened:
        timeStamp = subArrOrderOpened[0]
        data = subArrOrderOpened[1]
        data = datetime.strptime(data, "%d.%m.%Y").strftime("%Y-%m-%d")
        vremya = subArrOrderOpened[2]
        currency = subArrOrderOpened[3]
        count = subArrOrderOpened[4]
        price = subArrOrderOpened[5]
        dealPrice = count * price
        komiss = subArrOrderOpened[6]
        komiss_currency = subArrOrderOpened[7]
        dealType = subArrOrderOpened[8]
        dealStatus = subArrOrderOpened[9]
        orderType = subArrOrderOpened[10]
        orderID = subArrOrderOpened[11]
        OrdersArr.append([timeStamp, data, vremya, currency, count, price, dealPrice, dealType, dealStatus, orderID])

    # удаляем из БД предыдущие данные по портфелю
    # cursor.execute("""DELETE FROM""" + table_name)
    delete = "DELETE FROM %s" % (table_name)
    cursor.execute(delete)

    try:
        # создаём переменную с запросом
        insert = """INSERT INTO """ + table_name + """ (timestamp, date, time, CurrencyPair, Count, Price, DealPrice, DealType, Status, OrderID)
			values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s);"""
        # выполняем запрос
        print("Add data to table open_orders -  SUCCESS")
        cursor.executemany(insert, OrdersArr)

        db.commit()  # Чтобы сохранить данные в БД

        # закрываем курсор
        cursor.close()
        # закрываем соединение (если больше не оно не нужно)
        db.close()

    # добавим обработку ошибок
    except MySQLdb.Error as e:
        print("MySQL Error [%d]: %s" % (e.args[0], e.args[1]))


save2dbOrders()