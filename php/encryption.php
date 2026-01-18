<?php 
    define("SECRET", $_SESSION['FILE_SEC']);

    function getContentTypeFromFilename($filename) {
        $endung = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($endung) {
            case 'pdf': return 'application/pdf';
            case 'jpg': case 'jpeg': return 'image/jpeg';
            case 'png': return 'image/png';
            case 'gif': return 'image/gif';
            // ... weitere Content-Types hinzufügen ...
            default: return 'application/octet-stream'; // Generischer Content-Type für unbekannte Dateien
        }
    }

    function encrpyt_file($path, $fileName, $cont_to_encode, $secret){
        $methode = 'aes-256-cbc'; 
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($methode));
        $encrypted = openssl_encrypt(file_get_contents($cont_to_encode), $methode, $secret, 0, $iv);
        $encodedName = base64_encode($fileName) . 'x1x1x1x' . base64_encode($iv);
        $encodedName = str_replace(array('+', '/'), array('-', '_'), $encodedName);
        $targetFile = $path . $encodedName;
        return array($targetFile, $fileName, $encodedName, $encrypted);
    }

    function decrpyt_file($path, $file_to_decode, $secret){
        $datei_pfad = $path.$file_to_decode;
        $methode = 'aes-256-cbc'; 
        list($base64FileName, $base64Iv) = explode('x1x1x1x', basename($datei_pfad));
        $fileName   = base64_decode(str_replace(array('-', '_'), array('+', '/'), $base64FileName));
        $iv         = base64_decode(str_replace(array('-', '_'), array('+', '/'), $base64Iv));
        $crypted = file_get_contents($datei_pfad);
        $decrypted = openssl_decrypt($crypted, $methode, $secret, 0, $iv);
        $contentType = getContentTypeFromFilename($fileName);
        return array($contentType, $fileName, $decrypted);
    }