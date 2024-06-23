"""
yolo_detector.py

This module contains the YOLOv8Detector class, which performs face detection using the YOLOv8 model
and integrates face recognition using a provided FaceRecognizer instance.

Classes:
    YOLOv8Detector: Handles video capture, face detection, and recognition.
"""

import time
import cv2
import cvzone
import numpy as np
import os
from ultralytics import YOLO
from typing import Tuple, Union, List
from face_recognizer import IFaceRecognizer
import time
from datetime import datetime

class YOLOv8Detector:
    """
    Class for performing face detection using the YOLOv8 model.
    """

    def __init__(self, model_path: str, resolution: Tuple[int, int], face_recognizer: IFaceRecognizer):
        """
        Initialize the YOLOv8Detector.

        Args:
            model_path (str): Path to the YOLOv8 model weights.
            resolution (Tuple[int, int]): Resolution of the webcam feed (width, height).
            face_recognizer (IFaceRecognizer): FaceRecognizer instance for recognizing faces.
        """
        self.model = YOLO(model_path)
        self.resolution = resolution
        self.face_recognizer = face_recognizer
        self.detected_names = [] # Initialize list to store detected names

    def _configure_video_capture(self, capture_source: Union[int, str]) -> cv2.VideoCapture:
        """
        Configure the video capture source.

        Args:
            capture_source (Union[int, str]): Index of the camera to use or path to the video file.

        Returns:
            cv2.VideoCapture: Configured video capture object.
        """
        cap = cv2.VideoCapture(capture_source)
        cap.set(cv2.CAP_PROP_FRAME_WIDTH, self.resolution[0])
        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, self.resolution[1])
        return cap

    def _capture_photo(self, frame: np.ndarray) -> None:
        """
        Capture and save a photo at the 30-second mark.

        Args:
            frame (np.ndarray): The current video frame.
        """
        # Create the Attendance directory if it doesn't exist
        if not os.path.exists('Attendance'):
            os.makedirs('Attendance')

        # Generate the filename with the current date and time
        timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
        filename = os.path.join('Attendance', f'photo_{timestamp}.jpg')

        # Save the frame as an image file
        cv2.imwrite(filename, frame)

    def detect_and_display(self, cap: cv2.VideoCapture) -> None:
        """
        Perform face detection and recognition on video frames and display the results.

        Args:
            cap (cv2.VideoCapture): Video capture object.

        Returns:
            List[str]: List of student names.
        """
        start_time = time.time()
        photo_taken = False

        fps_start_time = start_time
        font = cv2.FONT_HERSHEY_SIMPLEX
        font_scale = 1
        font_color = (0, 0, 255)
        line_type = 2
        position_fps = (10, 90)
        position_num = (10, 30)
        position_obj = (10, 60)
        frame_skip = 2  # Process every 2nd frame
        frame_count = 0

        while True:
            current_time = time.time()
            elapsed_time = current_time - start_time

            if elapsed_time > 60:  # Run for 1 minute
                print("1 minute elapsed. Stopping the detection.")
                break

            success, frame = cap.read()
            if not success:
                break

            frame_count += 1
            if frame_count % frame_skip != 0:
                continue

            results = self.model(frame)

            fps_end_time = current_time
            fps = 1 / (fps_end_time - fps_start_time)
            fps_start_time = fps_end_time

            text_fps = f"FPS: {int(fps)}"
            num_objects = sum(len(r.boxes) for r in results)
            text_num_objects = f"Total number of people: {num_objects}"

            face_locations = []
            for r in results:
                for box in r.boxes:
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    cls = int(box.cls[0])
                    if cls == 0:
                        face_locations.append((y1, x2, y2, x1))

            recognized_faces = self.face_recognizer.recognize_faces(frame, face_locations)

            for name, confidence, (left, top, right, bottom) in recognized_faces:
                cvzone.cornerRect(frame, (left, top, right - left, bottom - top))
                cvzone.putTextRect(
                    frame,
                    f"{name} ({confidence:.2f})",
                    (max(0, left + 10), max(35, top - 20)),
                    scale=0.8,
                    colorR=(255, 0, 0),
                    font=cv2.FONT_HERSHEY_SIMPLEX,
                    colorB=(0, 0, 255),
                    offset=15,
                    thickness=2
                )
                if name not in self.detected_names and name != "Unknown":
                    self.detected_names.append(name)

            numAttendance = len(self.detected_names)
            text_num_attendance = f"Total number of attendees: {numAttendance}"
            cv2.putText(frame, text_num_attendance, position_num, font, font_scale, font_color, line_type)
            cv2.putText(frame, text_num_objects, position_obj, font, font_scale, font_color, line_type)
            cv2.putText(frame, text_fps, position_fps, font, font_scale, font_color, line_type)

            cv2.imshow("Detection", frame)

            # Capture a photo at the 30-second mark
            if elapsed_time >= 30 and not photo_taken:
                self._capture_photo(frame)
                photo_taken = True

            if cv2.waitKey(1) & 0xFF == ord('q'):
                break

        cap.release()
        cv2.destroyAllWindows()
