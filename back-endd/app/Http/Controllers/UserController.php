<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\kandidat;
use App\Models\periode;
use App\Models\pemilihan;
use App\Models\Voting;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;    //hash edit profile
use Illuminate\Support\Facades\Auth; // untuk auth
use Illuminate\Support\Facades\Validator; //untuk validasi menambahkan akun admin

class UserController extends Controller
{
    public function profile(Request $request)
    {
        //done
        // Ambil user yang sedang login
        $IdUser = $request->user->IdUser;
        
        $user = User::where('IdUser', $IdUser)->exists();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        // Ambil data user dari tabel user berdasarkan IdUser
        $userData = User::where('IdUser', $IdUser)->first();
    
        // Membuat respons JSON dengan data user
        $response = [
            'id' => $userData->IdUser,
            'nama' => $userData->nama,
            'nis' => $userData->nis,
        ];
    
        return response()->json($response, 200);
    }

    public function editprofile(Request $request, $IdUser)
    {
        // $IdUser didapatkan dari IdUser di Profile()
        // cek apakah di dalam tabel user dengan iduser yang diberikan ada
        $user = User::where('IdUser', $IdUser)->first();
    
        if ($user) {
            $validator = Validator::make($request->all(), [
                'nis' => 'nullable|min:5|max:5|unique:user',
                'nama' => 'required',
                'password' => 'required|min:8',
                'confirmation_password' => 'required|same:password',
            ]);
    
            if ($validator->fails()) {
                return messageError($validator->messages()->toArray());
            }
    
            $userData = $validator->validated();
    
            //data jika value nis tidak ada
            $updateData = [
                'nama' => $userData['nama'],
                'password' => Hash::make($userData['password'])
            ];
    
            //mengecek value nis jika kosong akan di lanjut, jika ada melakukan input nis
            if (isset($userData['nis'])) {
                $updateData['nis'] = $userData['nis'];
            }
    
            $update = User::where('IdUser', $IdUser)->update($updateData);
    
            return response()->json(['message' => 'Profile updated successfully'], 200);
        }
    
        return messageError($validator->messages()->toArray());
    }

    //function pengajuan kandidat
    public function kandidat(Request $request, $IdPemilihan)
    {
        //idpemilihan di dapatkan pada parameter request fe
        $validator = Validator::make($request->all(), [
            'visi' => 'required',
            'misi' => 'required',
            'gambar' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $IdUser = $request->user;

        $kandidat = Kandidat::where('IdUser', $IdUser->IdUser)
        ->where('IdPemilihan', $IdPemilihan)
        ->first();

        if ($kandidat) {
            // User sudah terdaftar dalam tabel kandidat maka tidak bisa mengajukan lagi
            return response()->json(['message' => 'User sudah registrasi'], 422);
        }
        // Upload gambar dan simpan nama file ke kolom gambar
        $gambar = $request->file('gambar');
        $gambarName = time().'.'.$gambar->extension();
        $gambar->move(public_path('uploads'), $gambarName);

        $pemilihan = Pemilihan::find($IdPemilihan);

        if (!$pemilihan) {
            return response()->json(['error' => 'Pemilihan not found'], 404);
        }

        // Simpan data calon kandidat ke database
        $kandidat = new Kandidat;
        $kandidat->IdUser = $IdUser->IdUser;
        $kandidat->IdPemilihan = $IdPemilihan;
        $kandidat->visi = $request->input('visi');
        $kandidat->misi = $request->input('misi');
        $kandidat->gambar = $gambarName;
        $kandidat->save();
    
        return response()->json(['message' => 'Data calon kandidat berhasil disimpan'], 201);
    }

    public function vote(Request $request, $IdPemilihan)
    {
        //done
        //cek apakah IdPemilihan berstatus aktif atau gak
        $status = DB::table('pemilihan')
        ->select('IdStatus')
        ->where('IdPemilihan', $IdPemilihan)
        ->first();

        //jika status aktif maka akan melanjutkan respon
        if ($status && $status->IdStatus == 1) {

            //mengecek apakah IdKandidat yang di berikan ada pada tabel kandidat atau tidak
            $validator = Validator::make($request->all(), [
                'IdKandidat' => 'required|exists:kandidat,IdKandidat'
            ]);
            
            if ($validator->fails()) {
                return messageError($validator->messages()->toArray());
            }
    
            //mengecek apakah IdKandidat yang di berikan ada pada tabel kandidat atau tidak
            $Pemilihan = Pemilihan::where('IdPemilihan', $IdPemilihan)->first();
            if (!$Pemilihan) {
                // Jika Pemilihan tidak ditemukan, kirim respons error
                return response()->json(['message' => 'Pemilihan tidak ditemukan'], 404);
            }
                    
            // Ambil user yang sedang login
            $IdUser = $request->user->IdUser;
    
            // Cek apakah user sudah melakukan voting pada pemilihan ini
            $existingVote = Voting::where('IdUser', $IdUser)
                ->where('IdPemilihan', $IdPemilihan)
                ->first();
    
            if ($existingVote) {
                return response()->json(['error' => 'User has already voted for this election'], 422);
            }
    
            // Cek apakah IdKandidat yang diberikan memiliki IdPemilihan yang sama dengan parameter yang diberikan
            $IdKandidat = $request->input('IdKandidat');
            $kandidat = Kandidat::where('IdKandidat', $IdKandidat)
                ->where('IdPemilihan', $IdPemilihan)
                ->first();
    
            if (!$kandidat) {
                return response()->json(['error' => 'IdKandidat does not exist in this Pemilihan'], 422);
            }
    
            // Buat voting baru
            $voting = new Voting();
            $voting->IdUser = $IdUser;
            $voting->IdKandidat = $request->input('IdKandidat');
            $voting->IdPemilihan = $IdPemilihan;
            $voting->WaktuVote = now();
            $voting->save();
    
            return response()->json(['message' => 'Vote recorded successfully'], 200);
        //jika status pemilihan belum di mulai akan mengembalkan nilai else
        } else {
            return response()->json(['message' => 'Pemilihan Belum Di Mulai'], 422);
        }        
    }

    public function dashboard($IdPemilihan)
    {
        //menampilkan voting masuk
        $votemasuk = DB::table('voting')
        ->join('user', 'voting.IdUser', '=', 'user.IdUser')
        ->join('kandidat', 'voting.IdKandidat', '=', 'kandidat.IdKandidat')
        ->join('pemilihan', 'voting.IdPemilihan', '=', 'pemilihan.IdPemilihan')
        ->select(
            'voting.IdVoting',
            'user.nama as NamaPemilih',
            DB::raw('(SELECT nama FROM user WHERE user.IdUser = kandidat.IdUser) AS VoteKandidat'),
            'pemilihan.nama as NamaPemilihan',
            'voting.WaktuVote'
            )
        ->where('pemilihan.IdPemilihan', $IdPemilihan)
        ->get();
            
        if ($votemasuk->isEmpty()) {
            return response()->json(['msg' => 'Pemilihan tidak ditemukan'], 404);
        }

        //jumlah vote yang masuk ke kandidat
        $totalvotekandidat = DB::table('voting')
        ->join('kandidat', 'voting.IdKandidat', '=', 'kandidat.IdKandidat')
        ->join('user', 'kandidat.IdUser', '=', 'user.IdUser')
        ->select(
            'voting.IdKandidat', 
            DB::raw('(SELECT nama FROM user WHERE user.IdUser = kandidat.IdUser) AS VoteKandidat'),
            DB::raw('COUNT(*) as JumlahVote')
        )
        ->where('voting.IdPemilihan', $IdPemilihan)
        ->groupBy('voting.IdKandidat', 'kandidat.IdUser')
        ->get();


        //total vote yang sudah masuk
        $totalvote = DB::table('voting')
        ->select(DB::raw('COUNT(*) as TotalVoting'))
        ->join('user', 'voting.IdUser', '=', 'user.IdUser')
        ->where('voting.IdPemilihan', $IdPemilihan)
        ->where('user.role', '!=', 'Admin')
        ->get();
    
        
        //total user yang belum vote
        $belumvote = DB::table('user')
            ->select(DB::raw('COUNT(*) as JumlahVotingBelumDilakukan'))
            ->where('role', '<>', 'Admin')
            ->whereNotIn('IdUser', function ($query) use ($IdPemilihan) {
                $query->select('IdUser')
                    ->from('voting')
                    ->where('IdPemilihan', $IdPemilihan);
            })
            ->get();
            
    return response()->json([
        'VoteMasuk' => $votemasuk, 
        'Voteperkandidat' => $totalvotekandidat,
        'totalvote' => $totalvote,
        'belumvote' => $belumvote,
    ]);
    }

    public function showpemilihan()
    {
        //done
        //tampil semua pemilihan
        $pemilihan = Pemilihan::all();

        return response()->json([
            "data" => $pemilihan,
        ],200);
    }
}
