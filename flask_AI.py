from flask import Flask, request, jsonify
from deepface import DeepFace
import base64
import cv2
import numpy as np
import mysql.connector
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# model_name = "VGG-Face"
model_name = "ArcFace"
model = DeepFace.build_model(model_name)

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="las_db"
)
cursor = db.cursor()


# threshold = 0.5
threshold = 0.68
@app.route('/upload_image', methods=['POST'])
def upload_image():
    
    data = request.json['image']
    image_data = base64.b64decode(data.split(',')[1])

    
    np_arr = np.frombuffer(image_data, np.uint8)
    img = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)

    
    mirrored_img = cv2.flip(img, 1)

    cursor.execute("SELECT id, photo_path FROM user_img")
    students = cursor.fetchall()

    matched_student_id = None
    min_distance = float("inf")

    for student_id, photo_path in students:
        db_img = cv2.imread(photo_path)
        if db_img is None:
            continue
        if db_img is not None and db_img.shape[2] == 4: 
            db_img = cv2.cvtColor(db_img, cv2.COLOR_BGRA2BGR)
    
        
        result_original = DeepFace.verify(img1_path=img, img2_path=db_img, model_name=model_name, enforce_detection=False)
        
        
        result_mirrored = DeepFace.verify(img1_path=mirrored_img, img2_path=db_img, model_name=model_name, enforce_detection=False)

        
        best_distance = min(result_original["distance"], result_mirrored["distance"])
        
        if best_distance < min_distance and best_distance < threshold:
            min_distance = best_distance
            matched_student_id = student_id

    print(matched_student_id)
    if matched_student_id:
        return jsonify({"verified": True, "student_id": matched_student_id})
    else:
        return jsonify({"verified": False, "message": "No matching student found"})

if __name__ == '__main__':
    app.run(debug=True)
