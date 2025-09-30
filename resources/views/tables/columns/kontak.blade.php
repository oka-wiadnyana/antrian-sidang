<div class="flex flex-col gap-1">
    @foreach ($getRecord()->pihak1 as $pihak1)
        @if (!empty($pihak1->pihak->telepon))
            @php
                $clean = preg_replace('/^0/', '62', $pihak1->pihak->telepon);
            @endphp
            <a href="https://wa.me/{{ $clean }}" target="_blank"
                style="display: inline-block; text-decoration: none;">
                <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px;  border: 1px solid #22c55e; color: #15803d; border-radius: 9999px; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); font-size: 12px; font-weight: 600; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#f0fdf4'" onmouseout="this.style.backgroundColor=''">
                    <x-heroicon-o-phone style="width: 16px; height: 16px;" />
                    <span>{{ $pihak1->nama ?? 'Nomor Telepon' }}</span>
                </div>
            </a>
        @endif
    @endforeach

    @foreach ($getRecord()->pihak2 as $pihak2)
        @if (!empty($pihak2->pihak->telepon))
            @php
                $clean = preg_replace('/^0/', '62', $pihak2->pihak->telepon);
            @endphp
            <a href="https://wa.me/{{ $clean }}" target="_blank"
                style="display: inline-block; text-decoration: none;">
                <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid #22c55e; color: #15803d; border-radius: 9999px; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); font-size: 12px; font-weight: 600; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#f0fdf4'" onmouseout="this.style.backgroundColor=''">
                    <x-heroicon-o-phone style="width: 16px; height: 16px;" />
                    <span>{{ $pihak2->nama ?? 'Nomor Telepon' }}</span>
                </div>
            </a>
        @endif
    @endforeach

    @foreach ($getRecord()->pihak_pengacara as $pihak_pengacara)
        @if (!empty($pihak_pengacara->pihak->telepon))
            @php
                $clean = preg_replace('/^0/', '62', $pihak_pengacara->pihak->telepon);
            @endphp
            <a href="https://wa.me/{{ $clean }}" target="_blank"
                style="display: inline-block; text-decoration: none;">
                <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid #22c55e; color: #15803d; border-radius: 9999px; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); font-size: 12px; font-weight: 600; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#f0fdf4'" onmouseout="this.style.backgroundColor=''">
                    <x-heroicon-o-phone style="width: 16px; height: 16px;" />
                    <span>Kuasa-{{ $pihak_pengacara->pihakDiwakili->nama ?? 'Nomor Telepon' }}</span>
                </div>
            </a>
        @endif
    @endforeach
</div>
