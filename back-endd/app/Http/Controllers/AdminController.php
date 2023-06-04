<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\kandidat;
use App\Models\periode;
use App\Models\pemilihan;
use App\Models\Voting;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;    //hash edit profile
use Illuminate\Support\Facades\Auth; // untuk auth
use Illuminate\Support\Facades\Validator; //untuk validasi menambahkan akun admin

class AdminController extends Controller
{
    
    // public function __construct()
    // {
    //     $this->middleware('auth.jwt');
    // }

    public function periode(Request $request)
    {
        //done
        // Validasi input yang diterima dari request
        $request->validate([
            'periode' => 'required',
        ]);
        
        // Buat instance model Periode
        $periode = new Periode;
        $periode->periode = $request->input('periode');
        
        // Simpan data ke dalam tabel periode
        $periode->save();
        
        return response()->json($periode, 201);
    }
    
    public function showsp(){
        //done
        $periode = Periode::all();
        $status = Status::all();

        return response()->json([
            "periode" => $periode,
            "status" => $status,
        200]);
    }
    
    public function showperiodebyid($id)
    {
        //done
        $periode = Periode::find($id);
        
        if (!$periode) {
            return response()->json(['message' => 'Periode not found'], 404);
        }
        
        return response()->json($periode);
    }
    
    
    public function user(Request $request)
    {
        //done
        $validator = validator::make($request->all(),[
            'nis' => 'required|min:5|unique:user',
            'nama' => 'required',
            'role' => 'required|in:Admin,User',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password',
        ]);
        
        if($validator->fails()){
            return messageError($validator->messages()->toArray());
        }
        
        $user = $validator->validated();
        
        User::create($user);
        
        $payload = [
            'nama' => $user['nama'],
            'role' => 'User',
            'iat' => now()->timestamp,
            'exp' => now()->timestamp + 7200,
        ];
        
        return response()->json([
            "data" => [
                'msg' => "berhasil membuat akun",
                'nama' => $user['nama'],
                'nis' => $user['nis'],
                'role' => $user['role'],
            ],
        ], 200);
        
    }
    
    public function showuser(){
        //done
        $user = user::all();

        return response()->json([
            "data" => $user,
        ],200);

    }

    public function showuserbyid($IdUser)
    {
        //done
        $user = user::find($IdUser);

        if (!$user) {
            return response()->json(['message' => 'user not found'], 404);
        }
        return response()->json([
            'data' => [$user]
        ],200);
    }
    
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
            'role' => $userData->role,
        ];
        
        return response()->json([
            'data' => [$response]
        ],200);
        //return response()->json($response, 200);
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

    public function pemilihan(Request $request)
    {
        //done
        //input pemilihan
        $validatedData = $request->validate([
            'nama' => 'required',
            'IdPeriode' => 'required|exists:periode,IdPeriode',
            'IdStatus' => 'required|exists:status,IdStatus',
            'deskripsi' => 'required',
        ]);

        $pemilihan = Pemilihan::create($validatedData);

        return response()->json([
            "data" => [
                'msg' => "berhasil membuat Pemilihan",
                'IdPemilihan' => $pemilihan['IdPemilihan'],
                'nama' => $pemilihan['nama'],
                'IdPeriode' => $pemilihan->periode->periode,
                'IdStatus' => $pemilihan->status->status,
                'deskripsi' => $pemilihan['deskripsi'],
            ],
        ], 201);
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
    
    public function showpemilihanbyid($id)
    {
        //done
        //tampil pemilihab by id
        $pemilihan = Pemilihan::find($id);
        
        if (!$pemilihan) {
            return response()->json(['error' => 'Pemilihan not found'], 404);
        }
        
        //dd($pemilihan->status->status);
        return response()->json([
            "data" => [
                'msg' => "tampil pemilihan by id",
                'IdPemilihan' => $pemilihan['IdPemilihan'],
                'nama' => $pemilihan['nama'],
                'IdPeriode' => $pemilihan->periode->periode,
                'IdStatus' => $pemilihan->status->status,
                'deskripsi' => $pemilihan['deskripsi'],
            ],
        ], 201);
    }
    
    public function showkandidat()
    {
        //done
        //tampil semua Kandidat
        //$Kandidat = Kandidat::all();

        $kandidat = DB::table('kandidat')
        ->select(
            'kandidat.IdKandidat',
            'user.nama AS nama_user',
            'pemilihan.nama AS nama_pemilihan',
            'kandidat.visi',
            'kandidat.misi',
            'kandidat.gambar',
            'kandidat.setuju'
        )
        ->join('user', 'kandidat.IdUser', '=', 'user.IdUser')
        ->join('pemilihan', 'kandidat.IdPemilihan', '=', 'pemilihan.IdPemilihan')
        ->get();
        return response()->json([
            "data" => $kandidat,
        ],200);
    }

    public function showkandidatbyid($id)
    {
        //done
        //tampil pemilihab by id
        $kandidat = kandidat::find($id);
        
        if (!$kandidat) {
            return response()->json(['error' => 'kandidat not found'], 404);
        }
        
        //dd($kandidat->status->status);
        return response()->json([
            "data" => [
                'msg' => "tampil kandidat by id",
                'Idkandidat' => $kandidat['IdKandidat'],
                'visi' => $kandidat['visi'],
                'misi' => $kandidat['misi'],
                'nama_kandidat' => $kandidat->user->nama,
                'nama_pemilihan' => $kandidat->pemilihan->nama,
                'gambar' => $kandidat['gambar'],
                'status' => $kandidat['setuju'],
            ],
        ], 201);
    }

    public function acckandidat($IdKandidat)
    {
        //done
        // Mencari kandidat berdasarkan IdKandidat
        $kandidat = Kandidat::find($IdKandidat);
        
        if (!$kandidat) {
            // Jika kandidat tidak ditemukan, kirim respons error
            return response()->json(['message' => 'Kandidat tidak ditemukan'], 404);
        }
        
        // Update kolom setuju menjadi "ya"
        $kandidat->setuju = 'ya';
        $kandidat->save();
    
        // Mengembalikan respons sukses
        return response()->json(['message' => 'Kandidat dengan IdKandidat '.$IdKandidat.' berhasil disetujui'], 200);
    }
    
    public function noacckandidat($IdKandidat)
    {
        //done
        // Mencari kandidat berdasarkan IdKandidat
        $kandidat = Kandidat::find($IdKandidat);
    
        if (!$kandidat) {
            // Jika kandidat tidak ditemukan, kirim respons error
            return response()->json(['message' => 'Kandidat tidak ditemukan'], 404);
        }
    
        // Update kolom setuju menjadi "ya"
        $kandidat->setuju = 'tidak';
        $kandidat->save();
    
        // Mengembalikan respons sukses
        return response()->json(['message' => 'Kandidat dengan IdKandidat '.$IdKandidat.' berhasil diupdate'], 200);
    }
    
    public function accPemilihan($IdPemilihan)
    {
        //done
        // Mencari Pemilihan berdasarkan IdPemilihan
        $Pemilihan = Pemilihan::find($IdPemilihan);
    
        if (!$Pemilihan) {
            // Jika Pemilihan tidak ditemukan, kirim respons error
            return response()->json(['message' => 'Pemilihan tidak ditemukan'], 404);
        }
    
        // Update kolom IdStatus menjadi aktif
        $Pemilihan->IdStatus = 1;
        $Pemilihan->save();
    
        // Mengembalikan respons sukses
        return response()->json(['message' => 'Pemilihan dengan IdPemilihan '.$IdPemilihan.' sekarang aktif'], 200);
    }
    
    public function noaccPemilihan($IdPemilihan)
    {
        //done
        // Mencari Pemilihan berdasarkan IdPemilihan
        $Pemilihan = Pemilihan::find($IdPemilihan);
    
        if (!$Pemilihan) {
            // Jika Pemilihan tidak ditemukan, kirim respons error
            return response()->json(['message' => 'Pemilihan tidak ditemukan'], 404);
        }
    
        // Update kolom IdStatus menjadi tidak aktif
        $Pemilihan->IdStatus = 3;
        $Pemilihan->save();
    
        // Mengembalikan respons sukses
        return response()->json(['message' => 'Pemilihan dengan IdPemilihan '.$IdPemilihan.' sekarang sudah tidak aktif'], 200);
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
            ->where('IdPemilihan', $IdPemilihan)
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
 
}
