import pickle
import sys
import json

# Load the pre-trained model
with open('svm_model.pkl', 'rb') as model_file:
    model = pickle.load(model_file)

# Get input data from PHP
input_data = json.loads(sys.argv[1])

# Perform prediction
prediction = model.predict([input_data])

# Return the prediction as JSON
print(json.dumps({'prediction': prediction.tolist()}))
