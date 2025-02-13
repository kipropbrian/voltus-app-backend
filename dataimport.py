# move into main laravel functionality
import csv
import sqlite3
import uuid

# Path to your CSV file
csv_file_path = '/path/to/your/csv/file.csv'

# Path to your SQLite database
db_path = 'database/database.sqlite'

# Connect to the SQLite database
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Open the CSV file and read the data
with open(csv_file_path, mode='r', encoding='utf-8') as csv_file:
    csv_reader = csv.DictReader(csv_file)
    
    for row in csv_reader:
        full_name = row['full_name']
        unique_id = str(uuid.uuid4())
        
        # Insert the full_name into the people table
        cursor.execute('''
            INSERT INTO people (uuid, name)
            VALUES (?, ?)
        ''', (unique_id, full_name,))

conn.commit()

conn.close()

print("Data inserted successfully!")