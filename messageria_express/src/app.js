const express = require('express');
const messageRoutes = require('./routes/messageRoutes');

const app = express();
app.use(express.json());
app.use((req, res, next) => {
  const start = Date.now();
  
  res.on('finish', () => {
    const duration = Date.now() - start;
    console.log([${new Date().toISOString()}] ${req.method} ${req.originalUrl} - ${res.statusCode} ${duration}ms);
  });
  
  next();
});

// Middleware para log de erros
app.use((err, req, res, next) => {
  console.error([${new Date().toISOString()}] ERROR: ${err.stack});
  next(err);
});
app.use('/api', messageRoutes);

app.listen(3000, () => {
  console.log('Server is running on port 3000');
});