<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Building;
use App\Models\CsStaff;
use App\Models\Facility;
use App\Models\Room;
use App\Models\BookingCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kampus.ac.id',
            'password' => Hash::make('password123'),
            'phone_number' => '081234567890',
            'role' => 'admin',
        ]);

        $peminjam = User::create([
            'name' => 'Mahasiswa Teladan',
            'email' => 'mahasiswa@kampus.ac.id',
            'password' => Hash::make('password123'),
            'phone_number' => '089876543210',
            'role' => 'peminjam',
        ]);
        
        $gedungA = Building::create(['name' => 'Gedung Kuliah A']);
        $gedungB = Building::create(['name' => 'Gedung Kuliah B']);
        $aula = Building::create(['name' => 'Auditorium Utama']);

        $cs1 = CsStaff::create(['name' => 'Pak Budi (CS)', 'phone_number' => '08111111111']);
        $cs2 = CsStaff::create(['name' => 'Bu Susi (CS)', 'phone_number' => '08222222222']);

        $catOrmawa = BookingCategory::create(['name' => 'Ormawa', 'description' => 'Kegiatan Organisasi Mahasiswa']);
        $catUmum = BookingCategory::create(['name' => 'Umum', 'description' => 'Pihak Luar Kampus']);
        $catFakultas = BookingCategory::create(['name' => 'Fakultas', 'description' => 'Kegiatan Akademik']);

        $facWifi = Facility::create(['name' => 'WiFi Kencang']);
        $facProyektor = Facility::create(['name' => 'Proyektor HDMI']);
        $facAC = Facility::create(['name' => 'AC Split']);
        $facSound = Facility::create(['name' => 'Sound System']);

        $room1 = Room::create([
            'building_id' => $gedungA->id,
            'cs_staff_id' => $cs1->id,
            'name' => 'Ruang Kelas A-101',
            'code' => 'A-101',
            'capacity' => 50,
            'status' => 'available',
        ]);

        $room1->facilities()->attach([$facWifi->id, $facProyektor->id, $facAC->id]);

        $room2 = Room::create([
            'building_id' => $aula->id,
            'cs_staff_id' => $cs2->id,
            'name' => 'Aula Serbaguna',
            'code' => 'AULA-01',
            'capacity' => 500,
            'status' => 'available',
        ]);
        $room2->facilities()->attach([$facSound->id, $facAC->id, $facWifi->id]);

        $room3 = Room::create([
            'building_id' => $gedungB->id,
            'cs_staff_id' => $cs1->id,
            'name' => 'Lab Komputer B-202',
            'code' => 'B-202',
            'capacity' => 30,
            'status' => 'unavailable', 
        ]);
        $room3->facilities()->attach([$facWifi->id, $facAC->id]);

        echo "Data Dummy Berhasil Dibuat! 🚀\n";
        echo "Admin: admin@kampus.ac.id / password123\n";
        echo "Mhs  : mahasiswa@kampus.ac.id / password123\n";
    }
}