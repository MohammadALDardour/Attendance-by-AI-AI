import os
import mysql.connector
from PIL import Image

# Database connection
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='grad_project'
)
cursor = conn.cursor()

# Folder containing images
folder_path = r'C:\Users\ASUS\PycharmProjects\GraduationProject\Production\student_images'

# Function to convert image to binary
def convert_to_binary(file_path):
    with open(file_path, 'rb') as file:
        binary_data = file.read()
    return binary_data

# Iterate through all files in the folder
for filename in os.listdir(folder_path):
    if filename.endswith(('.png', '.jpg', '.jpeg')):  # Adjust based on your image formats
        # Full file path
        file_path = os.path.join(folder_path, filename)

        # Convert image to binary
        binary_data = convert_to_binary(file_path)

        # Extract person name from filename (assuming filename is the person's name)
        person_name = os.path.splitext(filename)[0]

        # Insert into database
        cursor.execute("INSERT INTO students_images (student_id, image) VALUES (%s, %s)",
                       (person_name, binary_data))
        conn.commit()

# Close the cursor and connection
cursor.close()
conn.close()

print("Images uploaded successfully.")
