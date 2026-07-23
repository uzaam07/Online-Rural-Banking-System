from db_config import get_db_connection, close_db_connection

def test_connection():
    connection = get_db_connection()
    if connection:
        print("Successfully connected to MySQL database!")
        cursor = connection.cursor()
        cursor.execute("SELECT DATABASE();")
        database = cursor.fetchone()
        print(f"Connected to database: {database[0]}")
        cursor.close()
        close_db_connection(connection)
    else:
        print("Failed to connect to MySQL database!")

if __name__ == "__main__":
    test_connection() 
