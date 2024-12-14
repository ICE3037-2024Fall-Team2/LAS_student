from flask import Flask, request, jsonify
from deepface import DeepFace
import base64
import os
import boto3
import pymysql
import cv2
import numpy as np
import requests
from botocore.exceptions import ClientError
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Initialize ArcFace model
model_name = "ArcFace"
model = DeepFace.build_model(model_name)

# S3 client and bucket details
s3_client = boto3.client( 's3', 
    aws_access_key_id=os.getenv('AWS_ACCESS_KEY_ID'), 
    aws_secret_access_key=os.getenv('AWS_SECRET_ACCESS_KEY'),
    region_name=os.getenv('AWS_DEFAULT_REGION')
)

bucket_name = "lrsys-bucket"

# Database configuration
db_config = {
    "host": "lrsys-db.c18aw6e60k1x.ap-northeast-2.rds.amazonaws.com",
    "user": "admin",
    "password": "Wyq001102",
    "database": "las_db"
}

# Matching threshold
threshold = 0.68

def generate_presigned_url(s3_client, bucket_name, object_key, expiration=3600):
    try:
        response = s3_client.generate_presigned_url(
            "get_object",
            Params={"Bucket": bucket_name, "Key": object_key},
            ExpiresIn=expiration
        )
        return response
    except ClientError as e:
        app.logger.error(f"Error generating presigned URL: {e}")
        return None


@app.route('/upload_image', methods=['POST'])
def upload_image():
    try:
        data = request.json['image']
        image_data = base64.b64decode(data.split(',')[1])

    
        np_arr = np.frombuffer(image_data, np.uint8)
        img = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)


        mirrored_img = cv2.flip(img, 1)

        # Connect to RDS and fetch students
        with pymysql.connect(**db_config) as connection:
            with connection.cursor() as cursor:
                cursor.execute("SELECT id, photo_path FROM user_img")
                students = cursor.fetchall()

        # Image verification
        matched_student_id = None
        min_distance = float("inf")

        for student_id, photo_path in students:
            presigned_url = generate_presigned_url(s3_client, bucket_name, photo_path)
            if not presigned_url:
                app.logger.warning(f"Failed to generate presigned URL for {photo_path}")
                continue

            try:
                response = requests.get(presigned_url)
                response.raise_for_status()
                img_array = np.asarray(bytearray(response.content), dtype=np.uint8)
                db_img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)

                if db_img is None:
                    app.logger.warning(f"Failed to decode image from S3: {photo_path}")
                    continue

                if db_img.shape[2] == 4:
                    db_img = cv2.cvtColor(db_img, cv2.COLOR_BGRA2BGR)

            except Exception as e:
                app.logger.warning(f"Error loading image from S3: {photo_path}, Error: {e}")
                continue

            result_original = DeepFace.verify(
                img1_path=img, img2_path=db_img, model_name="ArcFace", enforce_detection=False
            )
            result_mirrored = DeepFace.verify(
                img1_path=mirrored_img, img2_path=db_img, model_name="ArcFace", enforce_detection=False
            )

            best_distance = min(result_original["distance"], result_mirrored["distance"])
            if best_distance < min_distance and best_distance < threshold:
                min_distance = best_distance
                matched_student_id = student_id

        if matched_student_id:
            return jsonify({"verified": True, "student_id": matched_student_id})
        else:
            return jsonify({"verified": False, "message": "No matching student found"})

    except ValueError as e:
        return jsonify({"error": str(e)}), 400
    except Exception as e:
        app.logger.error(f"Unexpected error: {e}")
        return jsonify({"error": "An unexpected error occurred"}), 500

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000, debug=True)
