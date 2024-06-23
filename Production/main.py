"""
main.py

Entry point for the YOLOv8-based face detection and recognition system.
This script initializes the components, parses command-line arguments,
and starts the detection and recognition process.

Usage:
    python main.py --webcam-resolution 650 550
"""

from yolo_detector import YOLOv8Detector
from face_recognizer import FaceRecognizer
from utils import parse_arguments, fetch_known_faces_from_db, save_detected_names_to_csv
from datetime import datetime
import mysql.connector
import sys


# def get_course_and_section():
#     # Database connection
#     conn = mysql.connector.connect(
#         host='localhost',
#         user='root',
#         password='',
#         database='grad_project'
#     )
#     cursor = conn.cursor()
#
#     # Assuming you want to get the course and section for the current day
#     current_day = datetime.now().strftime('%A').lower()  # e.g., 'monday'
#     cursor.execute("""
#         SELECT course_id, section_number
#         FROM courses
#         WHERE LOWER(day) = %s
#         LIMIT 1
#     """, (current_day,))
#
#     result = cursor.fetchone()
#     cursor.close()
#     conn.close()
#
#     if result:
#         return result[0], result[1]  # course_id, section_number
#     else:
#         print("No course found for the current day.")
#         sys.exit(1)  # Exit if no course is found
#
# def insert_attendance(student_ids, course_id, section_number):
#     # Database connection
#     conn = mysql.connector.connect(
#         host='localhost',
#         user='root',
#         password='',
#         database='grad_project'
#     )
#     cursor = conn.cursor()
#
#     # Get current date
#     current_date = datetime.now().date()
#
#     # Insert each detected student into the attendance table
#     for student_id in student_ids:
#         cursor.execute("""
#             INSERT INTO attendance (course_id, student_id, date, status, section_number)
#             VALUES (%s, %s, %s, %s, %s)
#         """, (course_id, student_id, current_date, 1, section_number))
#
#     conn.commit()
#     cursor.close()
#     conn.close()
#     print("Attendance records inserted successfully.")


def main() -> None:
    """
    Main function to run the object detection and recognition.

    Returns:
        None
    """
    args = parse_arguments()
    known_face_encodings, known_face_names = fetch_known_faces_from_db()
    face_recognizer = FaceRecognizer(known_face_encodings, known_face_names)
    detector = YOLOv8Detector(
        model_path='../YOLO-WEIGHT/best.pt',
        resolution=tuple(args.webcam_resolution),
        face_recognizer=face_recognizer
    )
    cap = detector._configure_video_capture(0)  # Default to webcam index 0
    detector.detect_and_display(cap)
    student_names = detector.detected_names  # Assuming the student names are the same as the known face names
    outputName = datetime.now().date().strftime('%Y-%m-%d')
    output_file = f'Attendance/Students names-{outputName}.csv'
    save_detected_names_to_csv(student_names, output_file)
    print(f"Detected names saved to {output_file}")

    # Retrieve course_id and section_number from the database
    # course_id, section_number = get_course_and_section()
    # insert_attendance(student_names, course_id, section_number)

    print("The process is done.")


if __name__ == "__main__":
    main()
