
const video = document.getElementById('video-feed');
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
const resultDiv = document.getElementById('result');

let model;

async function loadModel() {
model = await tf.loadGraphModel('your_model.json'); // Replace with your model file
}

async function startVideo() {
const stream = await navigator.mediaDevices.getUserMedia({ video: true });
video.srcObject = stream;
}

async function recognizeImage() {
ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

const imageData = tf.browser.fromPixels(canvas);
const resizedImage = imageData.resizeBilinear([224, 224]).toFloat().div(tf.scalar(255));

const predictions = await model.predict(resizedImage.expandDims());
const topK = predictions.argsort(axis=-1).reverse().slice(0, 5);

const labels = ['label1', 'label2', 'label3', 'label4', 'label5']; // Replace with your class labels

resultDiv.innerHTML = '';
for (let i = 0; i < topK.length; i++) {
    const classIndex = topK.dataSync()[i];
    const probability = predictions.dataSync()[i];
    resultDiv.innerHTML += `<p>${labels[classIndex]}: ${probability.toFixed(2)}</p>`;
}
}

// loadModel().then(() => {
//   startVideo();
//   setInterval(recognizeImage, 100); // Adjust interval as needed
// });

startVideo();
setInterval(recognizeImage, 100); 