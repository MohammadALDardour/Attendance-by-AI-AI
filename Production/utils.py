"""
utils.py

This module contains utility functions for the YOLOv8-based object detection and face recognition system.
It includes functions for parsing command-line arguments and loading known face encodings from a directory.

Functions:
    parse_arguments() -> argparse.Namespace: Parses command-line arguments.
    load_known_faces(images_path: str) -> Tuple[List[np.ndarray], List[str]]: Loads known face encodings and names from the specified directory.
    save_detected_names_to_csv(detected_names: List[str], output_file: str) -> None
"""

import argparse
import os
import face_recognition
import numpy as np
from typing import Tuple, List
import csv
from datetime import datetime
import cv2
import mysql.connector


def parse_arguments() -> argparse.Namespace:
    """
    Parse command-line arguments.

    Returns:
        argparse.Namespace: Namespace object containing parsed arguments.
    """
    parser = argparse.ArgumentParser(description="YOLOv8 live")
    parser.add_argument(
        "--webcam-resolution",
        default=[650, 550],
        nargs=2,
        type=int,
        help="Resolution (width and height) of the webcam feed. Default is set to [650, 550]."
    )
    return parser.parse_args()

def load_known_faces(images_path: str) -> Tuple[List[np.ndarray], List[str]]:
    """
    Load known face encodings and names from the specified directory.

    Args:
        images_path (str): Path to the directory containing images of known faces.

    Returns:
        Tuple[List[np.ndarray], List[str]]: List of known face encodings and corresponding names.
    """
    known_face_encodings = []
    known_face_names = []

    for filename in os.listdir(images_path):
        if filename.endswith((".jpg", ".png")):
            image_path = os.path.join(images_path, filename)
            image = face_recognition.load_image_file(image_path)
            encoding = face_recognition.face_encodings(image)[0]

            known_face_encodings.append(encoding)
            known_face_names.append(os.path.splitext(filename)[0])  # Use filename without extension as the name

    return known_face_encodings, known_face_names


def save_detected_names_to_csv(detected_names: List[str], output_file: str) -> None:
    """
    Save the detected names along with the current date and time to a CSV file.

    Args:
        detected_names (List[str]): List of detected names.
        student_names (List[str]): List of student names.
        output_file (str): Path to the output CSV file.

    Returns:
        None
    """
    with open(output_file, mode='w', newline='') as file:
        writer = csv.writer(file)
        writer.writerow(['student_id', 'date', 'time'])
        for student_name in detected_names:
            current_date = datetime.now().date().strftime('%Y-%m-%d')
            current_time = datetime.now().time().strftime('%H:%M:%S')
            writer.writerow([student_name, current_date, current_time])

        # Add a separate row for the number of attendance
        num_attendance = len(detected_names)
        writer.writerow(['Total Attendance', num_attendance])


def fetch_known_faces_from_db() -> Tuple[List[np.ndarray], List[str]]:
    """
    Fetch known face encodings and names from the MySQL database.

    Returns:
        Tuple[List[np.ndarray], List[str]]: List of known face encodings and corresponding names.
    """
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='grad_project'
    )
    cursor = conn.cursor()
    cursor.execute("SELECT student_name,image FROM students_images")
    results = cursor.fetchall()

    known_face_encodings = []
    known_face_names = []

    for person_name, binary_data in results:
        # Convert binary data to numpy array
        nparr = np.frombuffer(binary_data, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        encoding = face_recognition.face_encodings(img)[0]

        known_face_encodings.append(encoding)
        known_face_names.append(person_name)

    cursor.close()
    conn.close()
    return known_face_encodings, known_face_names