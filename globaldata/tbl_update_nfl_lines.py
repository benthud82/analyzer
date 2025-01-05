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
            # page.select_option('#pggcFilterBetType', index=1)
            # page.wait_for_timeout(2000)  # Wait for filter to apply
            
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
    # Convert fractions to decimals
    odds_text = convert_fraction_to_decimal(odds_text)
    
    # Check for "pk" and return 0
    if odds_text.lower() == 'pk':
        return 0.0
    
    # Use regex to extract the first valid float number
    match = re.search(r'[-+]?\d+(\.\d+)?', odds_text)
    if match:
        return float(match.group())
    else:
        print(f"Warning: Could not extract odds from text: {odds_text}")
        return 0.0  # Return a default value or handle the error as needed

def extract_over_under_odds(odds_text):
    # Convert fractions to decimals
    odds_text = convert_fraction_to_decimal(odds_text)
    
    # Check for "pk" and return 0
    if odds_text.lower() == 'pk':
        return 0.0
    
    # Use regex to extract the first valid float number
    match = re.search(r'[-+]?\d+(\.\d+)?', odds_text)
    if match:
        return float(match.group())
    else:
        print(f"Warning: Could not extract odds from text: {odds_text}")
        return 0.0  # Return a default value or handle the error as needed

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
                spread = extract_spread(odds_text)
                
                if spread is not None:
                    classes = link.get('class', [])
                    # Use any() to check for partial matches in class names
                    if any('Opener' in cls for cls in classes):
                        if any('a1c' in cls for cls in classes):
                            if 'pggc-away' in classes:
                                row_data['openaway'] = spread
                                row_data['openhome'] = -spread
                            elif 'pggc-home' in classes:
                                row_data['openhome'] = spread
                                row_data['openaway'] = -spread
                        elif any('a2c' in cls for cls in classes):
                            row_data['overopen'] = spread
                    elif any('Current' in cls for cls in classes):
                        if any('a1c' in cls for cls in classes):
                            if 'pggc-away' in classes:
                                row_data['currentaway'] = spread
                                row_data['currenthome'] = -spread
                            elif 'pggc-home' in classes:
                                row_data['currenthome'] = spread
                                row_data['currentaway'] = -spread
                        elif any('a2c' in cls for cls in classes):
                            row_data['overcurrent'] = spread
            
            # Consensus Data (Cash and Tickets)
            consensus_links = row.find_all('a', class_='pggc-link--consensus')
            for link in consensus_links:
                classes = link.get('class', [])
                text = link.text.strip().replace('%', '')

                # Determine the field based on class names and text content
                try:
                    if 'Cash' in str(classes):
                        if 'u' in text:
                            row_data['cashunder'] = int(text[1:])
                            row_data['cashover'] = 100 - row_data['cashunder']
                        elif 'o' in text:
                            row_data['cashover'] = int(text[1:])
                            row_data['cashunder'] = 100 - row_data['cashover']
                        if 'Away' in str(classes):
                            row_data['cashaway'] = int(text) if text != '-' else 0
                            row_data['cashhome'] = 100 - row_data['cashaway']
                        elif 'Home' in str(classes):
                            row_data['cashhome'] = int(text) if text != '-' else 0
                            row_data['cashaway'] = 100 - row_data['cashhome']
                    elif 'Ticket' in str(classes):
                        if 'u' in text:
                            row_data['ticketsunder'] = int(text[1:])
                            row_data['ticketsover'] = 100 - row_data['ticketsunder']
                        elif 'o' in text:
                            row_data['ticketsover'] = int(text[1:])
                            row_data['ticketsunder'] = 100 - row_data['ticketsover']
                        if 'Away' in str(classes):
                            row_data['ticketsaway'] = int(text) if text != '-' else 0
                            row_data['ticketshome'] = 100 - row_data['ticketsaway']
                        elif 'Home' in str(classes):
                            row_data['ticketshome'] = int(text) if text != '-' else 0
                            row_data['ticketsaway'] = 100 - row_data['ticketshome']
                except ValueError:
                    print(f"Error converting percentage: {text}")
            
            if len(row_data) == 20:  # Updated to reflect new fields
                # Ensure the values are in the correct order and types
                row_data_ordered = OrderedDict([
                    ('timedate', row_data.get('timedate')),
                    ('timetime', row_data.get('timetime')),
                    ('gamenumberaway', int(row_data.get('gamenumberaway', 0))),
                    ('gamenumberhome', int(row_data.get('gamenumberhome', 0))),
                    ('teamaway', row_data.get('teamaway', '')),
                    ('teamhome', row_data.get('teamhome', '')),
                    ('openaway', row_data.get('openaway', '')),
                    ('openhome', row_data.get('openhome', '')),
                    ('currentaway', row_data.get('currentaway', '')),
                    ('currenthome', row_data.get('currenthome', '')),
                    ('cashhome', int(row_data.get('cashhome', 0))),
                    ('cashaway', int(row_data.get('cashaway', 0))),
                    ('ticketshome', int(row_data.get('ticketshome', 0))),
                    ('ticketsaway', int(row_data.get('ticketsaway', 0))),
                    ('cashover', int(row_data.get('cashover', 0))),
                    ('ticketsover', int(row_data.get('ticketsover', 0))),
                    ('overopen', float(row_data.get('overopen', 0.0))),
                    ('overcurrent', float(row_data.get('overcurrent', 0.0))),
                    ('timepulled', row_data.get('timepulled'))
                ])

                columns = ', '.join(row_data_ordered.keys())
                placeholders = ', '.join(['%s'] * len(row_data_ordered))

                sql_insert = f"""INSERT INTO `lineswing`.`nfl_lines` ({columns}) 
                        VALUES ({placeholders})
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
                    print("SQL Query:", sql_insert)
                    print("Values:", list(row_data_ordered.values()))

    finally:
        # Clean up
        cnx.close()

if __name__ == "__main__":
    main()
