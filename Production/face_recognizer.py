"""
face_recognizer.py

This module contains the FaceRecognizer class, which performs face recognition using the face_recognition library.

Classes:
    IFaceRecognizer: Interface for face recognition implementations.
    FaceRecognizer: Concrete implementation of IFaceRecognizer using face_recognition library.
"""

import face_recognition
import numpy as np
from typing import List, Tuple


class IFaceRecognizer:
    """
    Interface for face recognition implementations.
    -Interface between face recognition and yolo_detector.py
    """

    def recognize_faces(self, frame: np.ndarray,
                        face_locations: List[Tuple[int, int, int, int]]
                        ) -> List[Tuple[str, float, Tuple[int, int, int, int]]]:
        """
        Recognize faces in the given frame at the specified locations.

        Args:  method name.__doc__
            frame (np.ndarray): The frame containing faces.
            face_locations (List[Tuple[int, int, int, int]]): List of face locations in the frame.

        Returns:
            List[Tuple[str, float, Tuple[int, int, int, int]]]: List of tuples containing name, confidence, and location.
        """
        raise NotImplementedError

class FaceRecognizer(IFaceRecognizer):
    """
    Concrete implementation of IFaceRecognizer using face_recognition library.
    """

    def __init__(self, known_face_encodings: List[np.ndarray], known_face_names: List[str]):
        """
        Initialize the FaceRecognizer.

        Args:
            known_face_encodings (List[np.ndarray]): List of known face encodings.
            known_face_names (List[str]): List of known face names.
        """
        self.__known_face_encodings = known_face_encodings
        self.__known_face_names = known_face_names

    def recognize_faces(self,
                        frame: np.ndarray,
                        face_locations: List[Tuple[int, int, int, int]]
                        ) -> List[Tuple[str, float, Tuple[int, int, int, int]]]:
        """
        Recognize faces in the given frame at the specified locations.

        Args:
            frame (np.ndarray): The frame containing faces.
            face_locations (List[Tuple[int, int, int, int]]): List of face locations in the frame.

        Returns:
            List[Tuple[str, float, Tuple[int, int, int, int]]]: List of tuples containing name, confidence, and location.
        """
        face_encodings = face_recognition.face_encodings(frame, face_locations)

        recognized_faces = []
        for (top, right, bottom, left), face_encoding in zip(face_locations, face_encodings):
            matches = face_recognition.compare_faces(self.__known_face_encodings, face_encoding)
            name = "Unknown"
            confidence = 0.0

            face_distances = face_recognition.face_distance(self.__known_face_encodings, face_encoding)
            best_match_index = np.argmin(face_distances)
            if matches[best_match_index]:
                name = self.__known_face_names[best_match_index]
                confidence = 1 - face_distances[best_match_index]

            recognized_faces.append((name, confidence, (left, top, right, bottom)))

        return recognized_faces
