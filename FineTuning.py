from ultralytics import YOLO

if __name__ == '__main__':
    """
    Script to train a YOLOv8 model using custom configuration and data.

    1. Initializes a YOLOv8 model for training.
    2. Trains the model with custom configuration and data for a specified number of epochs.

    """
    # Initialize YOLOv5 model for training
    model = YOLO('../YOLO-WEIGHT/yolov8l')

    # Train the model
    result = model.train(
        data='config.yaml',  # Path to the configuration file specifying training data
        epochs=5             # Number of epochs for training
    )
