exports.healthController = (req, res) => {
  res.status(200).json({
    message: "Service is running"
  });
}