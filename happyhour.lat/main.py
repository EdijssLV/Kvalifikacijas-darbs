from scrape import scrape_alkoutlet, scrape_SandW, scrape_LB, scrape_rimi, scrape_lats
from scrape import open_connection, clearDatabase, update_categories, close_connection
import subprocess

if __name__ == "__main__":
    open_connection()
    clearDatabase() 

    scrape_SandW()
    scrape_rimi()
    scrape_alkoutlet()
    scrape_lats()
    
    # scrape_LB()

    update_categories()
    close_connection()

    # subprocess.run(["php", "/var/www/user.happyhour.lat/log_prices.php"])
    # subprocess.run(["php", "/var/www/user.happyhour.lat/finalize_prices.php"])

