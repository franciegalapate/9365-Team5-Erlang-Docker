const mysql = require("mysql2");

const pool = mysql.createPool({
    host: "mysql",        // container service name
    user: "root",
    password: "root",     // change if different
    database: "mydb"      // your actual DB name
}).promise();

module.exports = pool;