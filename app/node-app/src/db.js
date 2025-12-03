const mysql = require("mysql2");

const pool = mysql.createPool({
    host: "db",       
    user: "appuser",
    password: "apppass",    
    database: "appdb"      
}).promise();

module.exports = pool;