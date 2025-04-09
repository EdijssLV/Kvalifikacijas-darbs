import sqlite3
from datetime import datetime

def update_categories():
    # Path to your SQLite database
    db_path = "/var/www/mysite/database/kabinets.db"
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()

    try:
        # Get today's date (YYYY-MM-DD format)
        today_date = datetime.now().strftime("%Y-%m-%d")

        # Check if data for today already exists in the categories table
        cursor.execute("""
            SELECT COUNT(*)
            FROM categories
            WHERE timestamp = ?
        """, (today_date,))
        if cursor.fetchone()[0] > 0:
            print("Data for today already exists in the categories table.")
            return

        # Calculate average price per category from Kabinets and nemainigs
        cursor.execute("""
            SELECT category, ROUND(AVG(PricePerLiter), 2) AS avg_price
            FROM (
                SELECT category, PricePerLiter FROM Kabinets
                UNION ALL
                SELECT category, PricePerLiter FROM nemainigs
            ) AS Combined
            WHERE category IS NOT NULL
            GROUP BY category;
        """)
        rows = cursor.fetchall()

        # Insert the averages into the categories table with the current date
        for row in rows:
            category = row[0]
            avg_price = row[1]

            # Ensure the category is not NULL
            if category:
                cursor.execute("""
                    INSERT INTO categories (category, avg_price, timestamp)
                    VALUES (?, ?, ?)
                """, (category, avg_price, today_date))

        # Commit the transaction
        conn.commit()
        print("Categories table updated successfully.")

    except Exception as e:
        print(f"An error occurred: {e}")
        conn.rollback()

    finally:
        conn.close()

if __name__ == "__main__":
    update_categories()