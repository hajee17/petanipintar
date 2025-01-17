<?php
session_start();

include("php/config.php");
if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
}

$userId = $_SESSION['id'];
$userSql = "SELECT latitude, longitude, alamat FROM users WHERE id = '$userId'";
$userResult = $con->query($userSql);
$userData = $userResult->fetch_assoc();

$userLatitude = $userData['latitude'];
$userLongitude = $userData['longitude'];
$userAlamat = $userData['alamat'];
$adaProgramDekat = false;

if (!empty($userAlamat)) {
    // Jika alamat tidak kosong, jalankan query SQL untuk mencari program tanam terdekat
    $sql = "SELECT *, 
            (6371 * 2 * ASIN(SQRT(POWER(SIN((latitude * PI() / 180) - ($userLatitude * PI() / 180)) / 2, 2) + COS($userLatitude * PI() / 180) * 
            COS(latitude * PI() / 180) * POWER(SIN((longitude * PI() / 180) - ($userLongitude * PI() / 180)) / 2, 2)))) AS distance
            FROM program_tanam
            HAVING distance < 50
            ORDER BY distance ASC
            LIMIT 5";

    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $adaProgramDekat = true;
    }
} else {
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Tanam</title>
    <link rel="icon" href="image/icon64.png" type="image/png">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <style>
        #map {
            height: 550px;
            border-radius: 15px;
        }

        .katalog-box-popup {
            height: 300px;
        }

        .katalog-box-popup .katalog-tanam-img {
            max-height: 150px;
        }

        .leaflet-popup-close-button {
            display: none !important;
        }

        .p-map {
            line-height: 21px;
            font-size: 16px;
        }

        .h3-map {
            font-size: 18px;
            font-weight: 600;
            font-family: 'Poppins';
        }

        .signin {
            font-family: 'Poppins';
        }

        @media (max-width: 575px) {
            #map {
                height: 360px;
            }
        }
    </style>
</head>

<body class="body-fixed">
    <header class="site-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-2">
                    <div class="header-logo">
                        <a href="menu.php">
                            <img src="image/logo_petanipintar.png" width="40" height="40" alt="Logo">
                        </a>
                    </div>
                </div>
                <div class="col-lg-10">
                    <div class="main-navigation">
                        <button class="menu-toggle"><span></span><span></span></button>
                        <nav class="header-menu">
                            <ul class="menu">
                                <li><a href="menu.php">PetaniPintar</a></li>
                                <li><a href="program-tanam.php">Program Tanam</a></li>
                                <li><a href="program-pupuk-subsidi.php">Pupuk Subsidi</a></li>
                                <li><a href="program-sewa-alat.php">Sewa Alat</a></li>
                                <li><a href="#">Forum</a></li>
                                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
                                    echo '<li><a href="admin/dashboard-1.php">Kelola</a></li>';
                                }?>
                                <li>
                                    <button onclick="window.location.href='profile.php'" class="signin">Profil</button>
                                    <button onclick="if(confirm('Apakah Anda yakin ingin keluar?')){window.location.href='login.php';}" class="signup">Keluar</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="viewport">
        <div id="js-scroll-content">
            <section class="main-banner" id="about">
                <div class="sec-wp">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="banner-text">
                                    <h2 class="h2-title">Mulai Program Tanam dengan <span>PetaniPintar</span></h2>
                                    <p>
                                        Memberdayakan petani dengan memberikan program tanam dan akses sumber daya yang sesuai dengan wilayah mereka. </p>
                                    <div class="banner-btn mt-4">
                                        <a href="#program" class="sec-btn">Mulai Program Tanam</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="banner-img-wp">
                                    <img class="img-rounded" src="image/illustration/program1.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="repeat-img" style="background-image: url(image/pattern1_background.png);">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="sec-title text-center mb-5">
                            <h2 class="h2-title mb-0">Program Tanam</h2>
                            <h2 class="h2-title"><span>PetaniPintar</span></h2>
                        </div>
                        <?php
                        if (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
                            echo '<div class="text-center mb-5">
                                <a href="edit-program-tanam.php" class="add">
                                    Ubah Program
                                </a>
                                <a href="add-program-tanam.php" class="add">
                                    + Tambah
                                </a>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
                <?php
                if ($adaProgramDekat) {
                ?>
                    <section class="mb-4" id="program">
                        <div class="sec-wp">
                            <div class="container">
                                <div class="sec-title">
                                    <h5 class="mb-4">Rekomendasi program tanam disekitar Anda</h5>
                                </div>
                                <div class="row katalog-tanam-slider">
                                    <div class="swiper-wrapper">
                                        <?php

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $jarak = $row['distance'];
                                                $jarakBulat = number_format($jarak, 1, '.', '');

                                                echo '<div class="col-lg-3 swiper-slide">
                                                <div class="katalog-box">
                                                <p class="p-katalog mb-1" style="text-align: right;">' . $jarakBulat . ' km</p>
                                                    <div style="background-image: url(image/tanaman/' . $row["gambar"] . ');" class="katalog-tanam-img back-img"></div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <h3 onclick="window.location.href=\'detail-program-tanam.php?id=' . $row["id"] . '\'" class="h3-title">' . $row["nama"] . '</h3>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <p class="p-katalog">Perkiraan<br>' . $row["waktu"] . ' bulan</p> 
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <p class="p-katalog">' . $row["daerah"] . '</p>
                                                        </div>
                                                        <p class="p-katalog">Rp. ' . number_format($row["hasil"], 0, ',', '.') . ' / ton</p>
                                                    </div>
                                                    <div>
                                                        <ul>
                                                            <li>
                                                                <button onclick="window.location.href=\'detail-program-tanam.php?id=' . $row["id"] . '\'" class="signin">Lihat Detail</button>';

                                                $sql_user_program = "SELECT * FROM user_program_tanam WHERE id_user = " . $_SESSION['id'] . " AND id_program_tanam = " . $row["id"];
                                                $result_user_program = $con->query($sql_user_program);
                                                if ($result_user_program->num_rows > 0) {
                                                    echo '<button onclick="window.location.href=\'kirim-hasil-panen.php.php?id=' . $row["id"] . '\'" class="signup">Kirim</button>';
                                                } else {
                                                    echo '<button onclick="window.location.href=\'mulai-program-tanam.php?id=' . $row["id"] . '\'" class="signup">Mulai</button>';
                                                }

                                                echo                    '</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>';
                                            }
                                        } else {
                                            echo "Tidak ada program tanam yang tersedia saat ini.";
                                        }
                                        ?>
                                    </div>
                                    <div class="swiper-button-wp">
                                        <div class="swiper-button-prev swiper-button">
                                            <i class="uil uil-angle-left"></i>
                                        </div>
                                        <div class="swiper-button-next swiper-button">
                                            <i class="uil uil-angle-right"></i>
                                        </div>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php
                }
                ?>

                <section class="default-banner" id="program">
                    <div class="sec-wp">
                        <div class="container">
                            <div class="sec-title">
                                <h5 class="mb-4">Semua program tanam</h5>
                            </div>
                            <div class="row katalog-tanam-slider">
                                <div class="swiper-wrapper">
                                    <?php
                                    include("php/config.php");
                                    $sql = "SELECT * FROM program_tanam";
                                    $result = $con->query($sql);

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $sql_user_program = "SELECT * FROM user_program_tanam WHERE id_user = " . $_SESSION['id'] . " AND id_program_tanam = " . $row["id"];
                                            $result_user_program = $con->query($sql_user_program);

                                            echo '<div class="col-lg-3 swiper-slide">
                                            <div class="katalog-box">
                                                <div style="background-image: url(image/tanaman/' . $row["gambar"] . ');" class="katalog-tanam-img back-img"></div>
                                                <h3 onclick="window.location.href=\'detail-program-tanam.php?id=' . $row["id"] . '\'" class="h3-title">' . $row["nama"] . '</h3>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <p class="p-katalog">Perkiraan<br>' . $row["waktu"] . ' bulan</p> 
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p class="p-katalog">' . $row["daerah"] . '</p>
                                                    </div>
                                                    <p class="p-katalog">Rp. ' . number_format($row["hasil"], 0, ',', '.') . ' / ton</p>
                                                </div>
                                                <div>
                                                    <ul>
                                                        <li>
                                                            <button onclick="window.location.href=\'detail-program-tanam.php?id=' . $row["id"] . '\'" class="signin">Lihat Detail</button>';

                                            if ($result_user_program->num_rows > 0) {
                                                echo '<button onclick="window.location.href=\'kirim-hasil-panen.php.php?id=' . $row["id"] . '\'" class="signup">Kirim</button>';
                                            } else {
                                                echo '<button onclick="window.location.href=\'mulai-program-tanam.php?id=' . $row["id"] . '\'" class="signup">Mulai</button>';
                                            }

                                            echo '</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>';
                                        }
                                    } else {
                                        echo "Tidak ada program tanam yang tersedia saat ini.";
                                    }
                                    ?>
                                </div>
                                <div class="swiper-button-wp">
                                    <div class="swiper-button-prev swiper-button">
                                        <i class="uil uil-angle-left"></i>
                                    </div>
                                    <div class="swiper-button-next swiper-button">
                                        <i class="uil uil-angle-right"></i>
                                    </div>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="default-banner" id="peta">
                    <div class="sec-wp">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="sec-title mb-4 text-center">
                                        <h3 class="h3-title mb-1"><span>Temukan Peluang Bertani</span></h3>
                                        <h3 class="h3-title">di Wilayah Anda</h3>
                                    </div>
                                    <div id="map" class="mb-4"></div>
                                    <div id="weather-info"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2">
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="banner-text mt-4">
                                            <p>
                                                Dapatkan informasi tentang potensi pertanian di daerah Anda dan manfaatkan program tanam untuk meningkatkan hasil panen dan
                                                memilih tanaman yang paling cocok di wilayah Anda.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                </section>


            </div>
            <footer class="site-footer" id="help">
                <div class="top-footer section">
                    <div class="sec-wp">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="footer-info">
                                        <div class="footer-logo">
                                            <a href="index.php">
                                                <img src="image/petanipintar_logo80.png" alt="Logo">
                                            </a>
                                        </div>
                                        <h5>Butuh Bantuan?</h5>
                                        <a>Hubungi kami untuk informasi lebih lanjut.</a>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="footer-flex-box">
                                        <div class="footer-menu">
                                            <h4 class="h4-title">Kontak</h4>
                                            <ul>
                                                <li><a href="#">petanipintar@gmail.com</a></li>
                                                <li><a href="#">+62 1234567890</a></li>
                                            </ul>
                                        </div>
                                        <div class="footer-menu food-nav-menu">
                                            <h4 class="h4-title">Menu</h4>
                                            <ul class="column-2">
                                                <li><a href="#about">Tentang Program</a></li>
                                                <li><a href="#program">Program Tanam</a></li>
                                                <li><a href="#peta">Peta Rekomendasi</a></li>
                                                <li><a href="#help">Pusat Bantuan</a></li>
                                            </ul>
                                        </div>
                                        <div class="footer-menu">
                                            <h4 class="h4-title">Informasi Lain</h4>
                                            <ul>
                                                <li><a href="#">FAQ</a></li>
                                                <li><a href="#">Kebijakan Privasi</a></li>
                                                <li><a href="#">Syarat dan Ketentuan</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="end-footer">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12 text-center mb-3">
                                <a>kamipetanipintar.com</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/jquery.mixitup.min.js"></script>
    <script src="js/swiper-bundle.min.js"></script>
    <script src="js/ScrollTrigger.min.js"></script>
    <script src="js/gsap.min.js"></script>
    <script src="main.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        function getWeather(latitude, longitude) {
            const apiUrl = `php/get-weather.php?lat=${latitude}&lon=${longitude}`;
    
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const weatherInfo = document.getElementById('weather-info');
                    weatherInfo.innerHTML = `
                    <div class="row">
                        <div class="col-lg-2">
                        </div>
                        <div class="col-lg-3">
                            <h5 class="mb-2">Cuaca di Wilayah Anda</h5>
                            <img src="https://openweathermap.org/img/wn/${data.weather[0].icon}.png" alt="Cuaca saat ini">
                            <p class="p-map mb-2">${data.weather[0].main}</p>
                            <p class="p-map">Suhu: ${data.main.temp}°C<br>Kondisi: ${data.weather[0].description}<br>Kecepatan Angin: ${data.wind.speed} m/s
                            <br>Kelembaban: ${data.main.humidity}%</p>
                        </div>
                        <div class="col-lg-6">
                            <h5 class="mb-3">Informasi Lainnya</h5>
                            <p class="p-map">Indeks UV: ${data.uvi}<br>Tekanan: ${data.main.pressure} hPa<br>Visibilitas: ${data.visibility} meter
                                <br>Matahari Terbit: ${new Date(data.sys.sunrise * 1000).toLocaleTimeString()}\
                                <br>Matahari Terbenam: ${new Date(data.sys.sunset * 1000).toLocaleTimeString()}
                                
                            </p>
                        </div>
                    </div>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching weather data:', error);
                });
        }
    
        var map = L.map('map');
    
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    
        var userLatitude = parseFloat(<?php echo $userLatitude; ?>);
        var userLongitude = parseFloat(<?php echo $userLongitude; ?>);
    
        if (!isNaN(userLatitude) && !isNaN(userLongitude) && (userLatitude !== 0 || userLongitude !== 0)) {
            map.setView([userLatitude, userLongitude], 9);
    
            var userMarker = L.marker([userLatitude, userLongitude]).addTo(map);
            userMarker.bindPopup('<p class="p-map m-0"><b>Lokasi Anda</b><br><?php echo $userAlamat; ?>').openPopup();
            userMarker.setZIndexOffset(1000);
    
            getWeather(userLatitude, userLongitude);
        } else {
            map.setView([-7.0, 110.0], 7);
        }
    
        function tampilkanProgramTanam() {
            $.getJSON("php/api.php", function(data) {
                data.forEach(function(program) {
                    var lat = parseFloat(program.latitude);
                    var lng = parseFloat(program.longitude);
    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        var hasilRupiah = 'Rp. ' + (program.hasil / 1000).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '.000';
    
                        var programBoxContent =
                            '<div class="katalog-box-popup">' +
                            '<div style="background-image: url(image/tanaman/' + program.gambar + ');" class="katalog-tanam-img back-img"></div>' +
                            '<h3 class="h3-map">' + program.nama + '</h3>' +
                            '<div>' +
                            '<div>' +
                            '<p class="p-map m-0">' + program.daerah + '</p>' +
                            '<p class="p-map m-0">Perkiraan ' + program.waktu + ' bulan</p>' +
                            '</div>' +
                            '<p class="p-map m-2">' + hasilRupiah + ' / ton</p>' +
                            '</div>' +
                            '<div class="text-center">' +
                            '<ul>' +
                            '<li>' +
                            '<button onclick="window.location.href=\'detail-program-tanam.php?id=' + program.id + '\'" class="signin">Lihat Detail</button>' +
                            '</li>' +
                            '</ul>' +
                            '</div>' +
                            '</div>';
    
                        var iconUrl = 'image/icon/default.png';
                        if (program.nama === 'Padi') {
                            iconUrl = 'image/icon/padi.png';
                        } else if (program.nama === 'Jagung') {
                            iconUrl = 'image/icon/jagung.png';
                        } else if (program.nama === 'Kentang') {
                            iconUrl = 'image/icon/kentang.png';
                        } else if (program.nama === 'Kedelai') {
                            iconUrl = 'image/icon/kedelai.png';
                        } else if (program.nama === 'Teh') {
                            iconUrl = 'image/icon/teh.png';
                        }
    
                        var programIcon = L.icon({
                            iconUrl: iconUrl,
                            iconSize: [40, 40],
                            iconAnchor: [15, 40],
                            popupAnchor: [0, -40],
                            shadowUrl: 'https://unpkg.com/leaflet@1.9.3/dist/images/marker-shadow.png',
                            shadowSize: [40, 40],
                            shadowAnchor: [15, 40]
                        });
    
                        var marker = L.marker([lat, lng], {
                            icon: programIcon
                        }).addTo(map);
                        marker.bindPopup(programBoxContent);
                    }
                });
            });
        }
    
        tampilkanProgramTanam();
    </script>
</body>

</html>