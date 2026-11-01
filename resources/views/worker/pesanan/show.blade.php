<x-worker-layout :header="'Detail Pesanan: ' . $pesanan->kode_pesanan">
    <div class="space-y-6" x-data="{
        previewModalOpen: false,
        previewImageUrl: '',
        openPreview(url) {
            this.previewImageUrl = url;
            this.previewModalOpen = true;
        }
    }">

        <!-- Header Nav & Status Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('worker.pesanan') }}"
                   class="p-2 rounded-xl bg-brand-surface border border-brand-border text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-white font-mono tracking-wide">
                            {{ $pesanan->kode_pesanan }}
                        </h1>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $pesanan->status->badgeColor() }}">
                            {{ $pesanan->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Ditugaskan pada {{ ($waktuDitugaskan ?? $pesanan->created_at)->translatedFormat('d F Y H:i') }} WIB
                    </p>
                </div>
            </div>

            <!-- Tombol Navigasi / Status Info -->
            <div class="flex items-center gap-3">
                @if($pesanan->status === \App\Enums\StatusPesanan::SedangDikerjakan)
                    <span class="px-3 py-1.5 rounded-xl bg-purple-500/10 border border-purple-500/30 text-purple-300 text-xs font-semibold flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-400 animate-ping"></span>
                        Sedang Anda Kerjakan
                    </span>
                @endif
            </div>
        </div>

        <!-- Banner Peringatan Revisi dari Admin / Customer (Jika Perlu Revisi / Dikomplain - Bug #11 & #22) -->
        @if(in_array($pesanan->status, [\App\Enums\StatusPesanan::PerluRevisiHasil, \App\Enums\StatusPesanan::Dikomplain]))
            <div class="p-5 rounded-2xl bg-orange-500/10 border-2 border-orange-500/40 text-orange-200 space-y-3">
                <div class="flex items-center gap-2 text-sm font-bold text-orange-400">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>
                        @if($pesanan->status === \App\Enums\StatusPesanan::Dikomplain)
                            KOMPLAIN HASIL PEKERJAAN DARI PELANGGAN
                        @else
                            PERMINTAAN REVISI HASIL DARI ADMIN
                        @endif
                    </span>
                </div>

                @if(!empty($catatanKomplainTerbaru))
                    <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-500/30 text-xs sm:text-sm text-rose-200">
                        <span class="font-semibold text-rose-300 block mb-1">💬 Komplain dari Pelanggan:</span>
                        <p class="leading-relaxed whitespace-pre-line">{{ $catatanKomplainTerbaru }}</p>
                    </div>
                @endif

                @if(!empty($catatanRevisiTerbaru))
                    <div class="p-4 rounded-xl bg-black/30 border border-orange-500/20 text-xs sm:text-sm text-slate-200">
                        <span class="font-semibold text-orange-300 block mb-1">🛠️ Instruksi / Catatan Revisi Admin:</span>
                        <p class="leading-relaxed whitespace-pre-line">{{ $catatanRevisiTerbaru }}</p>
                    </div>
                @elseif(empty($catatanKomplainTerbaru))
                    <div class="p-4 rounded-xl bg-black/30 border border-orange-500/20 text-xs sm:text-sm text-slate-200">
                        <span class="font-semibold text-orange-300 block mb-1">Catatan Koreksi:</span>
                        <p class="leading-relaxed whitespace-pre-line">Harap periksa kembali target rank atau quest yang belum terpenuhi di akun game.</p>
                    </div>
                @endif

                <p class="text-xs text-orange-300/80">
                    Silakan buka akun game kembali, perbaiki target yang diminta, lalu unggah foto bukti baru melalui formulir pelaporan di sebelah kanan.
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================================================= -->
            <!-- KOLOM KIRI (7/12): DATA AKUN & TARGET PESANAN                             -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. Kredensial Akun Game (Paling Krusial) -->
                <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-5"
                     x-data="{
                         showPassword: false,
                         passwordTimer: null,
                         passwordCountdown: 0,
                         copiedUser: false,
                         copiedPass: false,
                         togglePassword() {
                             this.showPassword = !this.showPassword;
                             if (this.showPassword) {
                                 this.passwordCountdown = 30;
                                 clearInterval(this.passwordTimer);
                                 this.passwordTimer = setInterval(() => {
                                     this.passwordCountdown--;
                                     if (this.passwordCountdown <= 0) {
                                         this.showPassword = false;
                                         clearInterval(this.passwordTimer);
                                         this.passwordTimer = null;
                                     }
                                 }, 1000);
                             } else {
                                 clearInterval(this.passwordTimer);
                                 this.passwordTimer = null;
                                 this.passwordCountdown = 0;
                             }
                         },
                         copyText(text, type) {
                             if (!navigator.clipboard) return;
                             navigator.clipboard.writeText(text).then(() => {
                                 if (type === 'user') {
                                     this.copiedUser = true;
                                     setTimeout(() => this.copiedUser = false, 2000);
                                 } else if (type === 'pass') {
                                     this.copiedPass = true;
                                     setTimeout(() => this.copiedPass = false, 2000);
                                 }
                             });
                         }
                     }">
                    
                    <div class="flex items-center justify-between border-b border-brand-border pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-brand-blue/20 text-brand-cyan flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Kredensial Login Akun Game</h3>
                                <p class="text-[11px] text-slate-400">Gunakan data berikut untuk login in-game pelanggan.</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30 uppercase tracking-wider">
                            Kerahasiaan Tinggi
                        </span>
                    </div>

                    <!-- Grid Info Game -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl bg-brand-surface border border-brand-border">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">User ID Game</span>
                            <span class="text-xs font-mono font-bold text-white break-all">{{ $pesanan->id_akun_game }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-brand-surface border border-brand-border">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Server / Zone ID</span>
                            <span class="text-xs font-mono font-bold text-white">{{ $pesanan->server_game ?? '-' }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-brand-surface border border-brand-border">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Nickname Game</span>
                            <span class="text-xs font-bold text-white truncate block">{{ $pesanan->nickname_game ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Login Via & Username/Email -->
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold block uppercase">Metode Login (Platform)</span>
                                <span class="text-xs font-bold text-brand-cyan">{{ $pesanan->login_via ?? 'Login Langsung' }}</span>
                            </div>
                            @if($pesanan->data_tambahan && isset($pesanan->data_tambahan['zone_id']))
                                <div class="text-xs text-slate-300">
                                    Zone ID Tambahan: <span class="font-mono font-bold text-white">{{ $pesanan->data_tambahan['zone_id'] }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Username / Email Login -->
                        <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 font-semibold block uppercase">Email / Username Login</span>
                                <span class="text-xs font-mono font-bold text-white truncate block">
                                    {{ $pesanan->username_akun_game ?? '-' }}
                                </span>
                            </div>
                            @if($pesanan->username_akun_game)
                                <button type="button"
                                        @click="copyText(@js($pesanan->username_akun_game), 'user')"
                                        class="px-2.5 py-1.5 rounded-lg bg-brand-card hover:bg-brand-surface border border-brand-border text-xs text-slate-300 hover:text-white flex items-center gap-1.5 transition-colors shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span x-text="copiedUser ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            @endif
                        </div>

                        <!-- Password Login (Decrypted for Worker / Cleared) -->
                        <div class="p-4 rounded-xl bg-brand-surface border border-brand-border">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] text-slate-400 font-semibold uppercase">Password Akun Game</span>
                                @if($pesanan->password_akun_game !== null)
                                    <span class="text-[10px] text-emerald-400 flex items-center gap-1 font-mono">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Aman Terdekripsi
                                    </span>
                                @endif
                            </div>

                            @if($pesanan->password_akun_game !== null)
                                <div class="flex items-center justify-between gap-3 p-2.5 rounded-lg bg-black/40 border border-brand-border">
                                    <div class="font-mono text-sm tracking-wider min-w-0 truncate">
                                        <span x-show="!showPassword" class="text-slate-400 select-none">••••••••••••••••</span>
                                        <span x-show="showPassword" x-cloak class="text-emerald-400 font-bold select-all">
                                            {{ $pesanan->password_akun_game }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button"
                                                @click="togglePassword()"
                                                class="p-1.5 rounded-lg bg-brand-surface hover:bg-brand-card text-slate-300 hover:text-white transition-colors"
                                                title="Lihat / Sembunyikan Password">
                                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                        <span x-show="showPassword" x-cloak class="text-[10px] text-amber-400 font-mono tabular-nums" x-text="passwordCountdown + 's'"></span>
                                        <button type="button"
                                                @click="copyText(@js($pesanan->password_akun_game), 'pass')"
                                                class="px-2.5 py-1.5 rounded-lg bg-brand-blue hover:bg-brand-blueHover text-xs font-semibold text-white flex items-center gap-1.5 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                            <span x-text="copiedPass ? 'Tersalin!' : 'Salin'"></span>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[11px] text-amber-400/80 mt-2 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Jaga integritas. Dilarang mengubah data akun in-game tanpa persetujuan pelanggan.
                                </p>
                            @else
                                <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Kredensial password telah dihapus permanen dari server pada {{ $pesanan->kredensial_dihapus_at?->translatedFormat('d M Y H:i') ?? 'waktu penyelesaian' }} demi perlindungan privasi akun pelanggan.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Catatan Tambahan Login / Catatan Customer -->
                    @if($pesanan->catatan_customer)
                        <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border">
                            <span class="text-[10px] text-slate-400 font-semibold block uppercase mb-1">Catatan Khusus dari Pelanggan:</span>
                            <p class="text-xs text-white leading-relaxed whitespace-pre-line">{{ $pesanan->catatan_customer }}</p>
                        </div>
                    @endif
                </div>

                <!-- 2. Rincian Paket & Target Layanan Joki -->
                <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-brand-border pb-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Target Paket & Layanan Joki</h3>
                            <p class="text-[11px] text-slate-400">{{ $pesanan->layanan?->game?->nama_game }} — {{ $pesanan->layanan?->nama_layanan }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @php
                            $orderItems = $pesanan->pesananItems->isNotEmpty() ? $pesanan->pesananItems : $pesanan->items;
                        @endphp
                        @forelse($orderItems as $item)
                            <div class="p-3 rounded-xl bg-brand-surface border border-brand-border flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-white block">
                                        {{ $item->nama_item ?: ($item->layananItem?->nama ?? 'Item Layanan Joki') }}
                                    </span>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                        @if($item->nilai)
                                            <span>Target / Nilai: <strong class="text-white">{{ $item->nilai }}</strong> {{ $item->layananItem?->satuan ?? '' }}</span>
                                        @endif
                                        @if($item->layananItem?->satuan && !$item->nilai)
                                            <span>Satuan: <strong class="text-white">{{ $item->layananItem->satuan }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($item->peran === 'awal')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                                            Kondisi Awal
                                        </span>
                                    @elseif($item->peran === 'tujuan')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-blue/15 text-brand-cyan border border-brand-blue/30">
                                            Target Akhir
                                        </span>
                                    @elseif($item->peran === 'dipilih')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                                            Paket Terpilih
                                        </span>
                                    @elseif($item->peran === 'tambahan')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                            Request Tambahan
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-500/15 text-slate-300 border border-slate-500/30">
                                            Target Joki
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-3 rounded-xl bg-brand-surface border border-brand-border flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-xs font-bold text-white block">{{ $pesanan->layanan?->nama_layanan ?? 'Paket Joki Standar' }}</span>
                                    <span class="text-[11px] text-slate-400">Game: {{ $pesanan->layanan?->game?->nama_game ?? 'Game' }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-brand-blue/10 text-brand-cyan border border-brand-blue/20">
                                    Layanan Utama
                                </span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. Riwayat Jejak Status Pesanan -->
                <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Timeline Riwayat Pengerjaan</span>
                    </h3>

                    <div class="space-y-3 relative before:absolute before:inset-0 before:left-3 before:w-0.5 before:bg-brand-border">
                        @foreach($pesanan->riwayatStatus->sortByDesc('id') as $riwayat)
                            <div class="relative flex items-start gap-3 pl-7">
                                <div class="absolute left-1.5 top-1.5 w-3 h-3 rounded-full bg-brand-blue border-2 border-brand-card -translate-x-1/2"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-white">
                                            {{ ucwords(str_replace('_', ' ', $riwayat->status_baru)) }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-mono">
                                            {{ $riwayat->created_at->translatedFormat('d M H:i') }}
                                        </span>
                                    </div>
                                    @if($riwayat->catatan)
                                        <p class="text-xs text-slate-300 mt-0.5 leading-relaxed bg-brand-surface/60 p-2 rounded-lg border border-brand-border/60">
                                            {{ $riwayat->catatan }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- KOLOM KANAN (5/12): AKSI WORKER & PELAPORAN BUKTI                         -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-5 space-y-6">

                <!-- 1. Kotak Aksi Utama Sesuai Status -->
                <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-5">
                    
                    <div class="border-b border-brand-border pb-3">
                        <h3 class="text-sm font-bold text-white">Tindakan Worker</h3>
                        <p class="text-[11px] text-slate-400">Ikuti tahapan kerja sesuai dengan status pesanan saat ini.</p>
                    </div>

                    <!-- KONDISI A: Status 'diproses' -> Worker harus Mulai Pengerjaan (W-04) -->
                    @if($pesanan->status === \App\Enums\StatusPesanan::Diproses)
                        <div class="space-y-4">
                            <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-200 leading-relaxed">
                                Pesanan telah ditugaskan kepada Anda. Silakan periksa kredensial login akun pelanggan di sebelah kiri, lalu tekan tombol di bawah ini untuk memulai sesi pengerjaan joki.
                            </div>

                            <form method="POST" action="{{ route('worker.pesanan.mulai', $pesanan->kode_pesanan) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full py-3.5 px-4 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Mulai Kerjakan Pesanan</span>
                                </button>
                            </form>
                        </div>

                    <!-- KONDISI B: Status 'sedang_dikerjakan' ATAU 'perlu_revisi_hasil' -> Form Laporan Bukti (W-05 & W-06) -->
                    @elseif(in_array($pesanan->status, [\App\Enums\StatusPesanan::SedangDikerjakan, \App\Enums\StatusPesanan::PerluRevisiHasil]))
                        <div class="space-y-4" x-data="{
                            previewUrl: null,
                            fileName: '',
                            handleFile(e) {
                                const file = e.target.files[0];
                                if (!file) return;
                                this.fileName = file.name;
                                this.previewUrl = URL.createObjectURL(file);
                            },
                            clearFile() {
                                this.previewUrl = null;
                                this.fileName = '';
                                this.$refs.fileInput.value = '';
                            }
                        }">
                            <div class="p-4 rounded-xl bg-purple-500/10 border border-purple-500/20 text-xs text-purple-200 leading-relaxed">
                                @if($pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil)
                                    <strong>Formulir Revisi Hasil:</strong> Unggah foto screenshot baru dari akun game yang telah diperbaiki sesuai catatan koreksi Admin.
                                @else
                                    <strong>Selesai Mengerjakan?</strong> Unggah foto tangkapan layar (screenshot) bukti kemenangan / rank akhir di in-game pelanggan untuk diverifikasi Admin.
                                @endif
                            </div>

                            <form method="POST"
                                  action="{{ route('worker.pesanan.lapor', $pesanan->kode_pesanan) }}"
                                  enctype="multipart/form-data"
                                  class="space-y-4">
                                @csrf

                                <!-- Input Foto Bukti -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 mb-1.5">
                                        Foto Bukti Penyelesaian (Screenshot In-Game) <span class="text-rose-400">*</span>
                                    </label>
                                    
                                    <div class="border-2 border-dashed border-brand-border rounded-xl p-4 text-center hover:border-brand-blue transition-colors bg-brand-surface relative">
                                        <input type="file"
                                               name="foto_bukti"
                                               x-ref="fileInput"
                                               @change="handleFile"
                                               accept="image/jpeg,image/png,image/webp"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                               required>
                                        
                                        <!-- State Belum Pilih File -->
                                        <div x-show="!previewUrl" class="space-y-2">
                                            <div class="w-10 h-10 mx-auto rounded-xl bg-brand-blue/10 text-brand-cyan flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <div class="text-xs text-slate-300">
                                                <span class="font-bold text-brand-cyan">Pilih foto</span> atau seret ke sini
                                            </div>
                                            <p class="text-[10px] text-slate-500">JPG, PNG, atau WebP (Maks. 2 MB)</p>
                                        </div>

                                        <!-- State Sudah Pilih File (Live Preview) -->
                                        <div x-show="previewUrl" x-cloak class="space-y-3">
                                            <img :src="previewUrl" alt="Preview Bukti" class="max-h-48 mx-auto rounded-lg object-contain border border-brand-border">
                                            <div class="flex items-center justify-between text-xs text-slate-300 px-2">
                                                <span class="truncate max-w-[200px] font-mono text-[11px]" x-text="fileName"></span>
                                                <button type="button" @click.stop="clearFile" class="text-rose-400 hover:text-rose-300 font-bold text-xs">
                                                    Ganti Foto
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('foto_bukti')
                                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Input Catatan Ringkasan -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 mb-1.5">
                                        Catatan / Keterangan Ringkas (Opsional)
                                    </label>
                                    <textarea name="catatan"
                                              rows="3"
                                              placeholder="Contoh: Akun sudah mencapai Mythic 25 bintang dengan win-rate 80%. Quest mingguan juga sudah selesai."
                                              class="w-full bg-brand-surface border border-brand-border rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-brand-blue focus:ring-1 focus:ring-brand-blue transition-colors">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Tombol Kirim -->
                                <button type="submit"
                                        class="w-full py-3 px-4 rounded-xl text-xs sm:text-sm font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $pesanan->status === \App\Enums\StatusPesanan::PerluRevisiHasil ? 'Kirim Bukti Revisi' : 'Kirim Laporan Penyelesaian' }}</span>
                                </button>
                            </form>
                        </div>

                    <!-- KONDISI C: Status 'laporan_masuk' -> Menunggu Verifikasi Admin -->
                    @elseif($pesanan->status === \App\Enums\StatusPesanan::LaporanMasuk)
                        <div class="p-4 rounded-xl bg-teal-500/10 border border-teal-500/20 text-xs text-teal-200 space-y-2">
                            <div class="font-bold text-teal-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                                Laporan Terkirim — Menunggu Verifikasi Admin
                            </div>
                            <p class="leading-relaxed">
                                Foto bukti penyelesaian Anda telah berhasil diunggah dan sedang dalam antrean verifikasi oleh Admin sebelum diteruskan ke pelanggan.
                            </p>
                        </div>

                    <!-- KONDISI D: Status 'worker_selesai' / 'menunggu_konfirmasi' -->
                    @elseif(in_array($pesanan->status, [\App\Enums\StatusPesanan::WorkerSelesai, \App\Enums\StatusPesanan::MenungguKonfirmasi]))
                        <div class="p-4 rounded-xl bg-sky-500/10 border border-sky-500/20 text-xs text-sky-200 space-y-2">
                            <div class="font-bold text-sky-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Laporan Disetujui — Menunggu Konfirmasi Pelanggan
                            </div>
                            <p class="leading-relaxed">
                                Admin telah menyetujui laporan Anda dan notifikasi penyelesaian telah diteruskan ke pelanggan. Pelanggan memiliki waktu 24 jam untuk memeriksa akun game.
                            </p>
                        </div>

                    <!-- KONDISI E: Status 'selesai' / 'hasil_disetujui' -->
                    @elseif(in_array($pesanan->status, [\App\Enums\StatusPesanan::HasilDisetujui, \App\Enums\StatusPesanan::Selesai]))
                        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-200 space-y-2">
                            <div class="font-bold text-emerald-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Tugas Joki Selesai Sempurna!
                            </div>
                            <p class="leading-relaxed">
                                Pelanggan telah mengonfirmasi penyelesaian tugas ini. Terima kasih atas kerja keras dan profesionalisme Anda.
                            </p>
                        </div>
                    @endif

                </div>

                <!-- 2. Riwayat Foto Bukti yang Pernah Diunggah -->
                <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4">
                    <div class="flex items-center justify-between border-b border-brand-border pb-3">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Berkas Laporan Penyelesaian</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">{{ $pesanan->laporanPenyelesaian->count() }} Berkas</span>
                    </div>

                    @if($pesanan->laporanPenyelesaian->count() > 0)
                        <div class="space-y-3">
                            @foreach($pesanan->laporanPenyelesaian->sortByDesc('id') as $laporan)
                                <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border flex items-start gap-3">
                                    <!-- Thumbnail Foto Bukti -->
                                    <div class="relative group cursor-pointer shrink-0"
                                         @click="openPreview(@js(route('worker.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $laporan->id])))">
                                        <img src="{{ route('worker.pesanan.bukti-selesai', [$pesanan->kode_pesanan, $laporan->id]) }}"
                                             alt="Bukti Penyelesaian"
                                             class="w-16 h-16 rounded-lg object-cover border border-brand-border group-hover:border-brand-blue transition-colors">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 rounded-lg flex items-center justify-center transition-opacity">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="text-xs font-bold text-white">Laporan #{{ $laporan->id }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border
                                                @if($laporan->status === \App\Enums\StatusLaporan::Terverifikasi) bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                                @elseif($laporan->status === \App\Enums\StatusLaporan::Ditolak) bg-rose-500/10 text-rose-400 border-rose-500/30
                                                @else bg-amber-500/10 text-amber-400 border-amber-500/30 @endif">
                                                {{ $laporan->status->label() }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400">
                                            Diunggah: {{ $laporan->created_at->translatedFormat('d M Y H:i') }}
                                        </p>
                                        @if($laporan->catatan)
                                            <p class="text-xs text-slate-300 mt-1.5 italic bg-black/20 p-2 rounded border border-brand-border/40">
                                                "{{ $laporan->catatan }}"
                                            </p>
                                        @endif
                                        @if($laporan->diverifikasi_at)
                                            <p class="text-[10px] text-slate-500 mt-1">
                                                Diverifikasi oleh {{ $laporan->verifikator?->nama ?? 'Admin' }} pada {{ $laporan->diverifikasi_at->translatedFormat('d M H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-500 text-xs">
                            Belum ada laporan bukti yang diunggah.
                        </div>
                    @endif
                </div>

            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- MODAL LIGHTBOX / FULLSCREEN FOTO BUKTI                                    -->
        <!-- ========================================================================= -->
        <div x-show="previewModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm"
             @keydown.escape.window="previewModalOpen = false">
            
            <div class="relative max-w-4xl w-full bg-brand-card border border-brand-border rounded-2xl overflow-hidden shadow-2xl p-4"
                 @click.away="previewModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-brand-border mb-3">
                    <span class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Pratinjau Foto Bukti Penyelesaian</span>
                    </span>
                    <button type="button" @click="previewModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex items-center justify-center bg-black/50 rounded-xl overflow-hidden max-h-[75vh]">
                    <img :src="previewImageUrl" alt="Detail Bukti" class="max-h-[75vh] w-auto object-contain">
                </div>

                <div class="mt-3 flex justify-end">
                    <button type="button" @click="previewModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-surface border border-brand-border text-slate-300 hover:text-white transition-colors">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-worker-layout>
