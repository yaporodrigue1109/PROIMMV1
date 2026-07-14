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
