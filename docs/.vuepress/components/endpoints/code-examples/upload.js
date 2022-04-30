axios.post("/media/upload", { file }).then(function(response) {
  console.log(response.data);
});
