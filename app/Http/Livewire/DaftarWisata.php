<?php

namespace App\Http\Livewire;

use App\Exports\WisataExport;
use App\Models\Kecamatan;
use App\Models\Wisata;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class DaftarWisata extends Component
{
    use WithPagination;
    public $search;
    public $sortBy = 'id_wisata';
    public $sortAsc = true;
    public $objWisata;

    public $deleteConfirmation = false;
    public $addConfirmation = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'id_wisata'],
        'sortAsc' => ['except' => true],
    ];

    protected $rules = [
        'objWisata.nama_wisata' => 'required|string|max:255',
        'objWisata.alamat' => 'required|string|max:255',
        'objWisata.id_kecamatan' => 'required',
    ];

    public function render()
    {
        $wisata = Wisata::with('kecamatan')
                ->leftjoin('kecamatan', 'wisata.id_kecamatan', '=', 'kecamatan.id_kecamatan')
                ->when($this->search, function($query){
                    $query->where('nama_wisata', 'like', '%'.$this->search.'%')
                        ->orWhere('alamat', 'like', '%'.$this->search.'%')
                        ->orWhere('kecamatan.nama_kecamatan', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->sortBy, $this->sortAsc ? 'asc' : 'desc');

        $wisata = $wisata->paginate(10);

        $kecamatan = Kecamatan::all();

        return view('livewire.daftar-wisata', [
            'wisata' => $wisata,
            'kecamatan' => $kecamatan,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy == $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortBy = $field;
    }

    public function deleteConfirmation($id_wisata)
    {
        $this->deleteConfirmation = $id_wisata;
        $wisata = Wisata::where('id_wisata', $id_wisata)->first();
    }

    public function hapusWisata(Wisata $wisata)
    {
        $wisata->delete();
        session()->flash('message', 'Data berhasil dihapus');
        $this->deleteConfirmation = false;
    }

    public function addConfirmation()
    {
        $this->reset(['objWisata']);
        $this->resetErrorBag();
        $this->addConfirmation = true;
    }

    public function editConfirmation(Wisata $wisata)
    {
        $this->resetErrorBag();
        $this->objWisata = $wisata;
        $this->addConfirmation = true;
    }

    public function saveWisata()
    {
        $this->validate();

        if (isset($this->objWisata->id_wisata)) {
            $this->objWisata->save();
            session()->flash('message', 'Data berhasil diubah');
            $this->addConfirmation = false;
        }
        else {
            Wisata::create($this->objWisata);
            session()->flash('message', 'Data berhasil ditambahkan');
            $this->addConfirmation = false;
        }
    }

    public function export()
    {
        return Excel::download(new WisataExport, 'DaftarWisata.xlsx');
    }
}
