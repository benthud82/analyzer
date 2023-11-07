from bs4 import BeautifulSoup
from selenium.webdriver.common.by import By
import time
import mysql.connector
from datetime import datetime
import glob
import pandas as pd
import re
import mysql.connector
from collections import OrderedDict

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager

options = Options()
options.add_argument('--disable-images')

driver = webdriver.Chrome('../chromedriver/chromedriver.exe')



# define an ordered dictionary with field names and default values
default_fields = OrderedDict([
    ('timedate', None),
    ('timetime', None),
    ('gamenumberaway', None),
    ('gamenumberhome', None),
    ('teamaway', None),
    ('teamhome', None),
    ('openaway', None),
    ('openhome', None),
    ('currentaway', None),
    ('currenthome', None),
    ('cashhome', None),
    ('cashaway', None),
    ('ticketshome', None),
    ('ticketsaway', None),
    ('timepulled', None)
])


cnx = mysql.connector.connect(user='bentley', password='dave41',
                            host='104.154.153.225',
                            database='lineswing')


cursor = cnx.cursor()

URL = 'https://pregame.com/game-center'
# time.sleep(8)
# list of columns that must be separated to o/u or ml
my_odds_list = ['open', 'current']
my_percent_list = ['cash', 'tickets']
# list of items that do not need to capture
my_skip_list = ['score', 'moves', 'picks']



driver.get(URL)


cursor = cnx.cursor()

time.sleep(5)
driver.find_element(By.ID, 'pggcFilterSport')
driver.find_element("link text", "MLB").click()

option_element = driver.find_element(By.CSS_SELECTOR, "#pggcFilterBetType > option:nth-child(2)")
option_element.click()


time.sleep(5)
driver.execute_script("window.scrollTo(0, 3000)")
time.sleep(5)
html = driver.page_source
soup = BeautifulSoup(html, 'html.parser')
rows = []
# There is a separate pggc-table for each game
game_divs = soup.findAll(class_="pggc-game")
for row in game_divs:
    allrowclass = row.attrs['class']
    if len(allrowclass) > 1: 
        if 'active' in allrowclass[1]  or 'cancelled' in allrowclass[1]:
            continue
    row_data = {}
    # pggc-col is each column in game row that has 2 cells per column
    for cell in row.findAll(class_="pggc-col"):
        # what is the other class after pggc-col?  this indicates that data stored in the td of table
        col_class = cell.attrs['class']
        # the second class is the data that is stored
        data_class = col_class[1]
        # strip data_class to pull just the data type
        data_class_stripped = data_class.replace("pggc-col--", "")
        data_class_stripped = data_class_stripped.replace("-", "")
        # if data_class_stripped == 'score' or data_class_stripped == 'moves':
        if data_class_stripped in my_skip_list:
            continue
        for all_p in cell.findAll(class_="pggc-col-data"):
            # one of these should have a2 or a3
            cell_class = all_p.attrs['class']
            cell_data = cell_class[1]
            cell_data_stripped = cell_data.replace("pggc-", "")
            # rename date and time data to configure to mysql standards
            if cell_data_stripped == 'col-data--date' or cell_data_stripped == 'col-data--time':
                cell_data_stripped = cell_data_stripped.replace(
                    "col-data--", "")
            if data_class_stripped in my_odds_list:
                # need to determine if ML or o/u.  'a2c' is o/u, 'a3c' is ML
                # loop through classes to see if contains a2c or a3c
                for lp_cell in cell_class:
                    if 'a2c' in lp_cell:
                        # this is the over/under
                        typeofbet = 'ou'
                        cell_data_stripped = typeofbet
                        exit
                    elif 'a3c' in lp_cell:
                        typeofbet = 'ml'
                        # who is/was favored, home or away
                        row_data[data_class_stripped +
                                'fav'] = cell_data_stripped
                        cell_data_stripped = cell_data_stripped + '-' + typeofbet
                        cell_data_stripped = ''  # reset variable to not write to row data
                        exit
            if data_class_stripped in my_percent_list:
                if all_p.text == '-':
                    continue
                # either tickets or money bet on overunder or ML.  Represent pecents bet on over, under, favorite, and underdog for ease of graph display
                if any("a2" in word for word in cell_class):
                    # a2 is overunder percent, pull first character to determine if over or under
                    if all_p.text[0] == 'o':
                        cell_data_stripped = 'over'
                        percent_stripped = int(
                            re.sub("[^0-9]", "", all_p.text))
                        row_data[data_class_stripped +
                                'under'] = 100 - percent_stripped
                    if all_p.text[0] == 'u':
                        cell_data_stripped = 'under'
                        percent_stripped = int(
                            re.sub("[^0-9]", "", all_p.text))
                        row_data[data_class_stripped +
                                'over'] = 100 - percent_stripped
                if any("a3" in word for word in cell_class):
                    # a3 is ML percent, who has the majority of percent
                    if any("home" in word for word in cell_class):
                        cell_data_stripped = 'home'
                        percent_stripped = int(
                            re.sub("[^0-9]", "", all_p.text))
                        row_data[data_class_stripped +
                                'away'] = 100 - percent_stripped
                    if any("away" in word for word in cell_class):
                        cell_data_stripped = 'away'
                        percent_stripped = int(
                            re.sub("[^0-9]", "", all_p.text))
                        row_data[data_class_stripped +
                                'home'] = 100 - percent_stripped
            if data_class_stripped == 'cash' or data_class_stripped == 'tickets':
                row_data[data_class_stripped +
                        cell_data_stripped] = percent_stripped
            elif data_class_stripped + cell_data_stripped == 'timedate':
                bad_date = all_p.text
                bad_date = datetime.strptime(bad_date, '%m/%d/%y')
                row_data[data_class_stripped +
                        cell_data_stripped] = bad_date.strftime("%Y-%m-%d")
            elif data_class_stripped + cell_data_stripped == 'timetime':
                bad_time = all_p.text
                bad_time = datetime.strptime(bad_time, '%I:%M %p')
                row_data[data_class_stripped +
                        cell_data_stripped] = bad_time.strftime("%H:%M")
            elif data_class_stripped == 'team':
                string = all_p.text
                row_data[data_class_stripped +
                        cell_data_stripped] = string[0:3]
            else:
                row_data[data_class_stripped + cell_data_stripped] = all_p.text
            data = all_p.text

    if len(row_data) == 14:
# update the default fields dictionary with any existing data points in row_data
        row_data_ordered = OrderedDict(default_fields)
        for key in row_data_ordered:
            if key in row_data:
                row_data_ordered[key] = row_data[key]
        
        row_data_ordered['timepulled'] = datetime.today().strftime("%Y-%m-%d %H:%M:%S")
        columns = ', '.join(row_data_ordered.keys())
        # Does this work when the columns are in a different order?!?!  None to test currently
        sql_insert = """INSERT INTO `lineswing`.`mlb_lines` (""" + columns + """) 
                VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                        ON DUPLICATE KEY UPDATE 
                            timedate = VALUES(timedate),
                            timetime = VALUES(timetime),
                            gamenumberaway = VALUES(gamenumberaway),
                            gamenumberhome = VALUES(gamenumberhome),
                            teamaway = VALUES(teamaway),
                            teamhome = VALUES(teamhome),
                            openaway = VALUES(openaway),
                            openhome = VALUES(openhome),
                            currentaway = VALUES(currentaway),
                            currenthome = VALUES(currenthome),
                            cashhome = VALUES(cashhome),
                            cashaway = VALUES(cashaway),
                            ticketshome = VALUES(ticketshome),
                            ticketsaway = VALUES(ticketsaway),
                            timepulled = VALUES(timepulled);"""

        try:
            cursor.execute(sql_insert, list(row_data_ordered.values()))
            cnx.commit()
        except:
            print(cursor.statement)

cnx.close()
driver.quit()
