KARAOKE BOOKING SYSTEM - INSTALLATION GUIDE

1. Start XAMPP (Apache & MySQL).
   - Semak fail dbconfig.php untuk nama DB dan connection

2. Pergi ke phpMyAdmin:
   - Klik New to add new database dengan nama karaoke_db
   - Klik "Import"
   - Upload fail: karaoke_db.sql
   - Klik "Go"

3. Letakkan folder sistem ke dalam:
   C:\xampp\htdocs\karaoke-reservation-web\

4. Klik index.html yang berada di dalam folder dan tukar :
   file:///C:/xampp/htdocs/karaoke-reservation-web/index.html kepada 
   http://localhost/karaoke-reservation-web/index.html 

   atau boleh langsung copy link http://localhost/karaoke-reservation-web/index.html dan paste di browser.

5. Login credentials:

   Admin:
   Email: admin00@gmail.com
   Password: admin00

   User:
   Email: oyen00@gmail.com
   Password: oyen00

6. Jika gagal login:
   - Semak fail dbconfig.php untuk nama DB dan connection
   - Pastikan semua table wujud dalam database
