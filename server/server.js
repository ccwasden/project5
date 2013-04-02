var express = require('express');
var app = express();

app.get('/', function(req, res){
  res.send('hello world');
});

app.listen(3000,"127.0.0.1");
console.log('Server running on port 3000');
// app.listen(1337, '127.0.0.1');
// console.log('Server running at http://127.0.0.1:1337/');