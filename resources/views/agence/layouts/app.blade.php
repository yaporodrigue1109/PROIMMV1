@extends('agence.layouts.base')

@php
    $authUser = auth('user')->user();
    $agenceName = $authUser?->name ?: 'Mon Agence';
    $agenceEmail = $authUser?->email ?: '';
    $nameParts = preg_split('/\s+/', trim($agenceName), -1, PREG_SPLIT_NO_EMPTY);
    $agenceInitials = collect($nameParts)
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('') ?: 'AG';
@endphp

@section('layout')
    @include('agence.partials.mobile-shell')

    <div class="app">
        @include('agence.partials.sidebar')

        <div class="main-content" id="mainContent">
            @php
                $impersonation = session('admin_agency_impersonation');
                $isAgencyImpersonation = auth('admin')->check()
                    && auth('user')->check()
                    && is_array($impersonation)
                    && (string) data_get($impersonation, 'admin_id') === (string) auth('admin')->id()
                    && (string) data_get($impersonation, 'user_id') === (string) auth('user')->id()
                    && (string) data_get($impersonation, 'agence_id') === (string) auth('user')->user()->agence_id;
            @endphp
            @if($isAgencyImpersonation)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:11px 20px;background:#fff7ed;border-bottom:1px solid #fed7aa;color:#9a3412;font-size:13px;position:sticky;top:0;z-index:60;">
                    <span>
                        <strong>Administrateur en consultation :</strong>
                        {{ $impersonation['admin_name'] ?? 'Administrateur' }} consulte l’agence
                        {{ $impersonation['agence_name'] ?? $authUser?->agence?->name }} via le compte de
                        {{ $authUser?->name ?? 'son responsable' }}.
                    </span>
                    <form method="POST" action="{{ route('agence.impersonation.stop') }}" style="margin:0;">
                        @csrf
                        <button type="submit" style="border:0;border-radius:9px;background:#9a3412;color:#fff;padding:8px 13px;font-weight:700;cursor:pointer;white-space:nowrap;">
                            Retour à l’administration
                        </button>
                    </form>
                </div>
            @endif
            <x-app-header
                    :user-initials="$agenceInitials"
                    :user-name="$agenceName"
                    :user-email="$agenceEmail"
                    :logout-route="route('agence.logout')"
                    :notification-count="0"
            />

            <main class="main">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        @if((bool) ($authUser?->agence?->parametrage?->double_validation ?? true))
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            const data = new FormData(form);
            const method = String(data.get('_method') || form.method || '').toLowerCase();

            if (method === 'delete' && !window.confirm('Confirmation de sécurité : voulez-vous vraiment effectuer cette suppression ? Cette action peut être irréversible.')) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        @endif

        function getRequest(route, id, type, value) {
            $.get({
                url: route,
                dataType: 'json',
                type: 'GET',
                success: function(data) {
                    if (type === 'select') {
                        const $select = $('#' + id);
                        const selectTag = data.select_tag || '';

                        $select.empty().append(selectTag);

                        const dropdown = document.querySelector(`.ui-select-dropdown[data-select-target="${id}"]`);
                        if (dropdown) {
                            const nativeSelect = document.getElementById(id);
                            const toggleLabel = dropdown.querySelector('.ui-dropdown-toggle span');
                            const menu = dropdown.querySelector('.ui-dropdown-menu');

                            if (menu) {
                                menu.innerHTML = '';
                            }

                            Array.from(nativeSelect?.options || []).forEach((option) => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'ui-dropdown-item' + (option.selected ? ' is-selected' : '');
                                item.dataset.value = option.value;
                                item.textContent = option.textContent.trim();
                                item.addEventListener('click', function () {
                                    if (!nativeSelect) {
                                        return;
                                    }

                                    nativeSelect.value = this.dataset.value;
                                    if (toggleLabel) {
                                        toggleLabel.textContent = this.textContent.trim();
                                    }

                                    menu?.querySelectorAll('.ui-dropdown-item').forEach((optionItem) => {
                                        optionItem.classList.remove('is-selected');
                                    });

                                    this.classList.add('is-selected');
                                    dropdown.classList.remove('open');
                                    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                });

                                if (menu) {
                                    menu.appendChild(item);
                                }
                            });

                            const selectedOption = nativeSelect?.selectedOptions?.[0] || nativeSelect?.options?.[0];
                            if (toggleLabel && selectedOption) {
                                toggleLabel.textContent = selectedOption.textContent.trim();
                            }
                        }
                    }
                },
            });
        }
    </script>
@endsection
