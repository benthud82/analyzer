from bs4 import BeautifulSoup
import time
import pymysql
from datetime import datetime
from collections import OrderedDict
import re
from playwright.sync_api import sync_playwright

# Keep your existing helper func tions and configurations
def convert_string(s):
    number = re.search(r'[-+]?[0-9]+', s)
    if number:
        return int(number.group())
    return None

# Keep your existing default_fields OrderedDict
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
    ('cashover', None),
    ('ticketsover', None),
    ('overopen', None),
    ('overcurrent', None),
    ('timepulled', None)
])

def get_page_data():
    with sync_playwright() as p:
        # Launch browser in visible mode
        browser = p.chromium.launch(headless=False)  # Changed to headless=False
        
        # Create a larger viewport for better visibility
        page = browser.new_page(viewport={'width': 1280, 'height': 800})
        
        try:
            # Navigate to the page with longer timeout and wait until network is idle
            page.goto('https://pregame.com/game-center', wait_until='networkidle', timeout=30000)
            
            # Wait for the sport dropdown to be ready
            page.wait_for_selector('#pggcFilterSport', state='visible', timeout=10000)
            
            # Select NFL and wait for change to take effect
            page.select_option('#pggcFilterSport', label='NFL')
            page.wait_for_timeout(2000)  # Wait for filter to apply
            
            # Select spread betting type and wait for change
            page.select_option('#pggcFilterBetType', index=1)
            page.wait_for_timeout(2000)  # Wait for filter to apply
            
            # Wait for games to appear with a more specific selector and longer timeout
            page.wait_for_selector('.pggc-game', state='visible', timeout=15000)
            
            # Additional wait to ensure all data is loaded
            page.wait_for_load_state('networkidle')
            
            # Get the page content
            content = page.content()
            
            return content
            
        except Exception as e:
            print(f"Error fetching page data: {e}")
            return None
        finally:
            browser.close()

# Add this helper function for date conversion
def convert_date_format(date_str):
    try:
        # Convert date from MM/DD/YY to YYYY-MM-DD
        date_obj = datetime.strptime(date_str, "%m/%d/%y")
        return date_obj.strftime("%Y-%m-%d")
    except Exception as e:
        print(f"Date conversion error: {e}")
        return None

# Add this helper function for time conversion
def convert_time_format(time_str):
    try:
        # Remove 'ET' and any extra spaces
        cleaned_time = time_str.replace(" ET", "").strip()
        # Convert time from "12:00 PM" format to "HH:MM:SS" format
        time_obj = datetime.strptime(cleaned_time, "%I:%M %p")
        # Format time as MySQL-compatible string
        return time_obj.strftime("%H:%M:00")  # Explicitly set seconds to '00'
    except Exception as e:
        print(f"Time conversion error for '{time_str}': {e}")
        return "00:00:00"  # Return a default valid time if conversion fails

def convert_fraction_to_decimal(odds_text):
    # Replace '½' or '1/2' with '.5'
    return odds_text.replace('½', '.5').replace('1/2', '.5')

def extract_spread(odds_text):
    # First convert any fractions to decimals
    odds_text = convert_fraction_to_decimal(odds_text)
    
    # Extract just the spread portion (before the juice/vig)
    if odds_text.startswith('-'):
        # For negative spreads, get everything up to the second minus sign
        parts = odds_text.split('-')
        return f"-{parts[1]}"
    else:
        # For positive spreads, get everything up to the first minus sign
        return odds_text.split('-')[0]

def extract_over_under_odds(odds_text):
    # Convert fractions to decimals
    odds_text = convert_fraction_to_decimal(odds_text)
    # Extract the numeric part before any 'o' or 'u'
    return float(odds_text.split('o')[0].split('u')[0])

def extract_over_under_percentage(text):
    try:
        # Convert the percentage to "over" format
        if text.startswith('o'):
            return int(text[1:])
        elif text.startswith('u'):
            return 100 - int(text[1:])
    except ValueError:
        print(f"Error converting percentage: {text}")
    return 0

def main():
    # Database connection using pymysql
    try:
        cnx = pymysql.connect(user='bentley', password='dave41',
                              host='104.154.153.225',
                              database='lineswing')
        cursor = cnx.cursor()
    except pymysql.MySQLError as err:
        print(f"Error: {err}")
        return

    # Lists for data processing
    my_odds_list = ['open', 'current']
    my_percent_list = ['cash', 'tickets']
    my_skip_list = ['score', 'moves', 'picks']

    try:
        # Get page content using Playwright
        html_content = get_page_data()
        
        if not html_content:
            print("Failed to fetch page content")
            return

        soup = BeautifulSoup(html_content, 'html.parser')
        
        # Rest of your existing parsing logic remains the same
        game_divs = soup.findAll(class_="pggc-game")
        for row in game_divs:
            allrowclass = row.attrs['class']
            if len(allrowclass) > 1: 
                if 'active' in allrowclass[1] or 'cancelled' in allrowclass[1]:
                    continue
                    
            row_data = {}
            
            # Date and Time
            row_data['timedate'] = convert_date_format(row.find('p', class_='pggc-col-data--date').text.strip())
            time_str = row.find('p', class_='pggc-col-data--time').text.strip()
            row_data['timetime'] = convert_time_format(time_str)
            
            # Game Numbers
            row_data['gamenumberaway'] = row.select_one('td.pggc-col--game-number p.pggc-away').text.strip()
            row_data['gamenumberhome'] = row.select_one('td.pggc-col--game-number p.pggc-home').text.strip()
            
            # Team Names
            team_elements = row.find_all('p', class_='pggc-col-data')
            for elem in team_elements:
                if 'pggc-away' in elem.get('class', []) and elem.text.strip().isalpha():
                    row_data['teamaway'] = elem.text.strip()
                elif 'pggc-home' in elem.get('class', []) and elem.text.strip().isalpha():
                    row_data['teamhome'] = elem.text.strip()
            
            # Odds (Open and Current)
            odds_links = row.find_all('a', class_='pggc-link--odds')
            for link in odds_links:
                odds_text = link.text.strip()
                
                if 'pggc-away' in link.get('class', []):
                    if 'openaway' not in row_data:
                        row_data['openaway'] = extract_spread(odds_text)
                    else:
                        row_data['currentaway'] = extract_spread(odds_text)
                elif 'pggc-home' in link.get('class', []):
                    if 'openhome' not in row_data:
                        row_data['openhome'] = extract_spread(odds_text)
                    else:
                        row_data['currenthome'] = extract_spread(odds_text)

                # Extract over/under odds
                if 'Opener' in link.get('class', []):
                    if 'pggc-home' in link.get('class', []):
                        row_data['overopen'] = extract_over_under_odds(odds_text)
                elif 'Current' in link.get('class', []):
                    if 'pggc-home' in link.get('class', []):
                        row_data['overcurrent'] = extract_over_under_odds(odds_text)
            
            # Consensus Data (Cash and Tickets)
            consensus_links = row.find_all('a', class_='pggc-link--consensus')
            for link in consensus_links:
                classes = link.get('class', [])
                text = link.text.strip().replace('%', '')
                
                # Determine the field based on specific class names
                try:
                    if 'e220957-a1-All-Cash-Away' in str(classes):
                        row_data['cashaway'] = int(text) if text != '-' else 0
                    elif 'e220957-a2-All-Cash-Home' in str(classes):
                        row_data['cashhome'] = int(text) if text != '-' else 0
                    elif 'e220957-a1-All-Ticket-Away' in str(classes):
                        row_data['ticketsaway'] = int(text) if text != '-' else 0
                    elif 'e220957-a2-All-Ticket-Home' in str(classes):
                        row_data['ticketshome'] = int(text) if text != '-' else 0
                    elif 'e220957-a2-All-Cash-Home' in str(classes):
                        row_data['cashover'] = extract_over_under_percentage(text)
                    elif 'e220957-a2-All-Ticket-Home' in str(classes):
                        row_data['ticketsover'] = extract_over_under_percentage(text)
                except ValueError:
                    value = 0
            
            if len(row_data) == 18:  # Updated to reflect new fields
                row_data_ordered = OrderedDict(default_fields)
                for key in row_data_ordered:
                    if key in row_data:
                        row_data_ordered[key] = row_data[key]
                
                row_data_ordered['timepulled'] = datetime.today().strftime("%Y-%m-%d %H:%M:%S")
                columns = ', '.join(row_data_ordered.keys())
                
                sql_insert = """INSERT INTO `lineswing`.`nfl_lines` (""" + columns + """) 
                        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
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
                            cashover = VALUES(cashover),
                            ticketsover = VALUES(ticketsover),
                            overopen = VALUES(overopen),
                            overcurrent = VALUES(overcurrent),
                            timepulled = VALUES(timepulled);"""

                try:
                    cursor.execute(sql_insert, list(row_data_ordered.values()))
                    cnx.commit()
                except Exception as e:
                    print(f"Error executing SQL: {e}")
                    print(cursor.statement)

    finally:
        # Clean up
        cnx.close()

if __name__ == "__main__":
    main()
