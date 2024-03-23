<?php
// file konfigurasi
require_once "config.php";

class Calculator //deklarasi class
{
    private $conn;

    // Constructor untuk menginisialisasi koneksi ke database
    public function __construct($servername, $username, $password, $dbname)
    {
        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Koneksi gagal: " . $this->conn->connect_error);
        }
    }

    // Method untuk melakukan perhitungan
    public function hitung($angka1, $angka2, $operasi)
    {
        switch ($operasi) {
            case "tambah":
                return $angka1 + $angka2;
            case "kurang":
                return $angka1 - $angka2;
            case "kali":
                return $angka1 * $angka2;
            case "bagi":
                if ($angka2 != 0) {
                    return $angka1 / $angka2;
                } else {
                    return "Tidak dapat melakukan pembagian. Angka kedua tidak boleh nol.";
                }
        }
    }

    // Method untuk menyimpan riwayat perhitungan ke database
    public function simpanRiwayat($operand1, $operator, $operand2, $hasil)
    {
        $sql = "INSERT INTO riwayat_perhitungan (operand1, operator, operand2, hasil) VALUES ('$operand1', '$operator', '$operand2', '$hasil')";
        $this->conn->query($sql);
    }

    // Method untuk mengambil riwayat perhitungan dari database
    public function getRiwayat()
    {
        $sql = "SELECT * FROM riwayat_perhitungan";
        return $this->conn->query($sql);
    }

    // Method untuk menghapus riwayat perhitungan dari database
    public function hapusRiwayat()
    {
        $sql = "DELETE FROM riwayat_perhitungan";
        $this->conn->query($sql);
    }

    // Method untuk menutup koneksi database
    public function __destruct()
    {
        $this->conn->close();
    }
}

// Membuat objek kalkulator
$calculator = new Calculator($servername, $username, $password, $dbname);

// ambil nilai dari form
if (isset($_POST['hitung'])) {
    $angka1 = $_POST['angka1'];
    $angka2 = $_POST['angka2'];
    $operasi = $_POST['operasi'];

    //memeriksa kelengkapan kolom
    if (empty($angka1) || empty($angka2)) {
        $hasil = "Kolom belum diisi.";
    } else {
        // Hitung hasil menggunakan metode hitung dari objek $calculator
        $hasil = $calculator->hitung($angka1, $angka2, $operasi);
        // Simpan riwayat perhitungan menggunakan metode simpanRiwayat dari objek $calculator
        $calculator->simpanRiwayat($angka1, $operasi, $angka2, $hasil);
    }
}

if (isset($_POST['hapus_riwayat'])) {
    $calculator->hapusRiwayat();
}

// Mendapatkan riwayat perhitungan
$result = $calculator->getRiwayat();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator</title>
    <link rel="stylesheet" href="mystyle.css" />
    <script>
        function isiAngka1(nilai) {
            document.getElementById('angka1').value = nilai;
        }
    </script>
</head>

<body>
    <div class="isi">
        <!-- form kalkulator -->
        <h1 class="judul">Kalkulator</h1>
        <form action="" method="post">
            <input class="input" id="angka1" type="text" name="angka1">
            <input class="input" id="angka2" type="text" name="angka2">
            <select class="operasi" name="operasi" id="">
                <option value="tambah"> + </option>
                <option value="kurang"> - </option>
                <option value="kali"> × </option>
                <option value="bagi"> ÷ </option>
            </select>
            <input class="hitung" type="submit" name="hitung" value="Hitung">
        </form>
        <h2>Hasil : <?php echo isset($hasil) ? $hasil : ''; ?></h2>

        <!-- //form riwayat -->
        <div class="riwayat">
            <h2>Riwayat</h2>
            <ul>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $symbol = "";
                        switch ($row["operator"]) {
                            case "tambah":
                                $symbol = "+";
                                break;
                            case "kurang":
                                $symbol = "-";
                                break;
                            case "kali":
                                $symbol = "×";
                                break;
                            case "bagi":
                                $symbol = "÷";
                                break;
                        }
                        echo "<li><a href='javascript:void(0);' onclick='isiAngka1(\"" . htmlspecialchars($row["hasil"]) . "\")'>" . $row["operand1"] . " " . $symbol . " " . $row["operand2"] . " = " . $row["hasil"] . "</a></li>";
                    }
                } else {
                    echo "Tidak ada riwayat perhitungan.";
                }
                ?>
            </ul>
            <!-- form hapus riwayat -->
            <form action="" method="post">
                <input type="submit" name="hapus_riwayat" value="Hapus Riwayat">
            </form>
        </div>
    </div>
</body>

</html>