"""
YOLOv8 Live Object Detection

This script performs real-time object detection using the YOLOv8 model.
It allows users to perform object detection on a live video stream from a webcam or from a video file.

Requirements:
-requirements.txt
- Python 3.11
- GPU: NVIDIA RTX >= 30.X
- NVIDIA CUDA

Installation:
You can install the required Python libraries using pip:
pip install -r requirement.txt
"""
import argparse

import math

import time

from typing import Tuple, Union

import cv2
import cvzone

from ultralytics import YOLO




def parse_arguments() -> argparse.Namespace:
    """
    Parse command-line arguments.

    Returns:
        argparse.Namespace: Namespace object containing parsed arguments.
    """
    parser = argparse.ArgumentParser(description="YOLOv8 live")
    parser.add_argument(
        "--webcam-resolution",
        default=[950, 850],
        nargs=2,
        type=int,
        help="Resolution (width and height) of the webcam feed. Default is set to [950, 850]."
    )
    return parser.parse_args()


class YOLOv8Detector:
    """
    Class for performing object detection using the YOLOv8 model.
    """

    def __init__(self, model_path: str, resolution: Tuple[int, int]):
        """
        Initialize the YOLOv8Detector.

        Args:
            model_path (str): Path to the YOLOv8 model weights.
            resolution (Tuple[int, int]): Resolution of the webcam feed (width, height).
            face_recognizer (FaceRecognizer): FaceRecognizer instance for recognizing faces.
        """
        self.model = YOLO(model_path)
        self.resolution = resolution


    def configure_video_capture(self, capture_source: Union[int, str]) -> cv2.VideoCapture:
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

    def detect_and_display(self, cap: cv2.VideoCapture) -> None:
        """
        Perform object detection on video frames and display the results.

        Args:
            cap (cv2.VideoCapture): Video capture object.

        Returns:
            None
        """
        fps_start_time = time.time()
        font = cv2.FONT_HERSHEY_SIMPLEX
        font_scale = 1
        font_color = (0, 0, 255)  # Red color
        line_type = 2
        line_height = 30
        position_fps = (10, 60)
        position_num = (10, 30)

        while True:
            success, frame = cap.read()
            if not success:
                break

            results = self.model(frame)

            fps_end_time = time.time()
            fps = 1 / (fps_end_time - fps_start_time)
            fps_start_time = fps_end_time

            text_fps = f"FPS: {fps:.2f}"
            num_objects = sum(len(r.boxes) for r in results)
            text_num_objects = f"Total number of attendees: {num_objects}"

            for r in results:
                for box in r.boxes:
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    w, h = x2 - x1, y2 - y1
                    confidence = math.ceil(box.conf[0] * 100) / 100
                    cls = int(box.cls[0])

                    if cls == 0:  # Assuming class 0 is for faces
                        cvzone.cornerRect(frame, (x1, y1, w, h))
                        cvzone.putTextRect(
                            frame,
                            f"Face {confidence}",
                            (max(0, x1 + 10), max(35, y1 - 20)),
                            scale=0.8,
                            colorR=(255, 0, 0),
                            font=cv2.FONT_HERSHEY_SIMPLEX,
                            colorB=(0, 0, 255),
                            offset=15,
                            thickness=2
                        )

            cv2.putText(frame, text_num_objects, position_num, font, font_scale, font_color, line_type)
            cv2.putText(frame, text_fps, position_fps, font, font_scale, font_color, line_type)

            cv2.imshow("Detection", frame)
            if cv2.waitKey(1) & 0xFF == ord('q'):
                break

        cap.release()
        cv2.destroyAllWindows()


def main() -> None:
    """
    Main function to run the object detection.

    Returns:
        None
    """
    args = parse_arguments()
    detector = YOLOv8Detector(model_path='../YOLO-WEIGHT/best.pt', resolution=tuple(args.webcam_resolution))
    cap = detector.configure_video_capture(0)  # Default to webcam index 0
    detector.detect_and_display(cap)


if __name__ == "__main__":
    main()
