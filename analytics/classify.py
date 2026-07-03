import sys
import json
import os
import numpy as np
from sklearn.ensemble import RandomForestClassifier

# Path ng cached model — isinasave sa analytics folder mismo
MODEL_PATH = os.path.join(os.path.dirname(__file__), 'model_cache.pkl')

def get_model():
    """
    I-load ang cached model kung meron.
    Kung wala pa, i-train at i-save para sa susunod.
    Dahil dito, isang beses lang nag-retrain — mas mabilis sa production.
    """
    try:
        import joblib

        if os.path.exists(MODEL_PATH):
            # I-load ang existing trained model
            return joblib.load(MODEL_PATH)

        # Wala pang cached model — i-train ngayon at i-save
        model = train_model()
        joblib.dump(model, MODEL_PATH)
        return model

    except ImportError:
        # Walang joblib — i-train na lang without caching
        return train_model()


def train_model():
    """
    I-train ang Random Forest classifier base sa DepEd grading thresholds:
    - Low risk:      90 - 100  (Outstanding / Very Satisfactory)
    - Moderate risk: 75 - 89   (Satisfactory / Fairly Satisfactory)
    - High risk:     below 75  (Did Not Meet Expectations)
    """
    X_train = np.array([
        # Low risk (90-100)
        [90.0], [91.5], [92.0], [93.5], [94.0],
        [95.0], [96.5], [97.0], [98.5], [99.0],
        [90.5], [92.5], [94.5], [96.0], [98.0],
        # Moderate risk (75-89)
        [75.0], [76.5], [78.0], [80.0], [82.5],
        [84.0], [85.5], [87.0], [88.5], [89.0],
        [76.0], [79.0], [81.0], [83.0], [86.0],
        # High risk (below 75)
        [74.9], [72.0], [70.0], [68.5], [65.0],
        [62.0], [60.0], [58.5], [55.0], [50.0],
        [73.0], [69.0], [66.0], [63.0], [57.0],
    ])

    y_train = np.array(
        [0] * 15 +  # low
        [1] * 15 +  # moderate
        [2] * 15    # high
    )

    model = RandomForestClassifier(
        n_estimators=100,
        random_state=42,
        max_depth=5
    )
    model.fit(X_train, y_train)
    return model


def classify_students(grades_data, model):
    """
    I-classify ang bawat student base sa kanilang average grade.
    """
    label_map = {0: 'low', 1: 'moderate', 2: 'high'}
    results   = []

    for student in grades_data:
        student_id    = student['student_id']
        average_grade = float(student['average_grade'])
        prediction    = model.predict([[average_grade]])[0]
        probabilities = model.predict_proba([[average_grade]])[0]

        results.append({
            'student_id':    student_id,
            'average_grade': average_grade,
            'risk_level':    label_map[prediction],
            'confidence':    round(float(max(probabilities)) * 100, 2),
        })

    return results


def main():
    if len(sys.argv) < 3:
        print('Usage: classify.py <input_file> <output_file>')
        return

    input_file  = sys.argv[1]
    output_file = sys.argv[2]

    # I-load ang grades data
    try:
        with open(input_file, 'r') as f:
            grades_data = json.load(f)
    except Exception as e:
        print(f'Error reading input file: {e}')
        return

    if not grades_data:
        results = []
    else:
        # I-load o i-train ang model (cached kung available)
        model   = get_model()
        results = classify_students(grades_data, model)

    # I-save ang results
    try:
        with open(output_file, 'w') as f:
            json.dump(results, f)
    except Exception as e:
        print(f'Error writing output file: {e}')


if __name__ == '__main__':
    main()