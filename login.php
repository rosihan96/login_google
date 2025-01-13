<?php
    //koneksi
    $conn = mysqli_connect('localhost', 'root', '', 'tutor') or die ('Gagal terhubung ke database');

    session_start();

    // library Google
    require_once 'vendor/autoload.php';

    // Konfigurasi client Google
    $client = new Google_Client();
    
    //non-aktifkan fitur ini ketika file diupload ke server online
    $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
    
    $client->setClientId('....'); // Ganti dengan Client ID Anda
    $client->setClientSecret('....'); // Ganti dengan Client Secret Anda
    $client->setRedirectUri('http://localhost/login_google/login.php'); // Ganti dengan Redirect URI Anda
    
    $client->addScope('email');
    $client->addScope('profile');

    // Proses login
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        // Ambil data pengguna
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $_SESSION['id'] = $google_account_info->id;
        $_SESSION['email'] = $google_account_info->email;
        $_SESSION['name'] = $google_account_info->name;

        $g_id = $_SESSION['id'];
        $g_email = $_SESSION['email'];
        $g_name = $_SESSION['name'];

        $currtime = date('Y-m-d H:i:s');

        //jika id sudah ada di tabel users, maka lakukan update data
        //jika id belum ada di tabel users, maka lakukan insert data

        $query_check = 'SELECT * FROM users WHERE oauth_id = "'.$g_id.'"';
        $run_query = mysqli_query($conn, $query_check);
        $d = mysqli_fetch_object($run_query);

        if($d){
            $update = 'UPDATE users SET fullname = "'.$g_name.'", email = "'.$g_email.'", lastlogin = "'.$currtime.'" WHERE oauth_id = "'.$g_id.'"';
            $run_update = mysqli_query($conn, $update);
        }else{
            $insert = 'INSERT INTO users (fullname, email, oauth_id, created_at) VALUE ("'.$g_name.'", "'.$g_email.'", "'.$g_id.'", "'.$currtime.'")';
            $run_insert = mysqli_query($conn, $insert);
        }
        header('Location: index.php');
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with Google</title>
</head>
<body>
    <div class="container">
        <a href="<?=  $client->createAuthUrl(); ?>">
            <img src="sso-google.png" alt="tombol login" width="40%">
        </a>
    </div>
</body>
</html>
