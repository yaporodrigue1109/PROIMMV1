@extends('agence.layouts.app')

@section('title', 'Dépense agence')

@section('content')

    <section class="page">

        <header class="page-header">
            <div class="page-header-copy">
                <div class="page-heading">
                    <div>
                        <h2>Dépense agence</h2>
                    </div>
                </div>
            </div>

            <div class="page-actions">
                <a href="{{ route('agence.caisse.index') }}" class="btn btn-outline">
                    Retour caisse
                </a>

                <button class="btn btn-secondary">
                    {{ now()->format('d/m/Y') }}
                </button>
            </div>
        </header>

        <div class="payment-shortcuts">

            <a href="{{ route('agence.caisse.loyer') }}"
               class="payment-shortcut">

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.5"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>

                <span>Loyers</span>

            </a>

            <a href="{{ route('agence.caisse.maintenance') }}"
               class="payment-shortcut ">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                </svg>

                <span>Maintenance</span>

            </a>

            <a href="{{ route('agence.caisse.depense.agence') }}"
               class="payment-shortcut active">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                </svg>

                <span>Dépenses agence</span>

            </a>

            <a href="{{ route('agence.caisse.vente.bien') }}"
               class="payment-shortcut">

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.5"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>

                <span>Vente de biens</span>

            </a>

        </div>

        <div class="agency-expense-wrapper">

            <div class="agency-expense-card">
                <div class="agency-expense-card-header">
                    <div>
                        <h3>Nouvelle dépense agence</h3>
                        <p>Ajoutez plusieurs dépenses puis validez le décaissement global.</p>
                    </div>
                </div>

                <form id="expense-form" action="#" method="POST">
                    @csrf

                    <div class="form-grid u-form-grid-2">

                        <label class="form-field">
                            <span>Catégorie</span>
                            <select id="expense-category">
                                <option value="">Sélectionner une catégorie</option>
                                <option value="Paiement facture">Paiement facture</option>
                                <option value="Fournitures">Fournitures</option>
                                <option value="Transport">Transport</option>
                                <option value="Communication">Communication</option>
                                <option value="Divers">Divers</option>
                            </select>
                        </label>

                        <label class="form-field">
                            <span>Référence</span>
                            <input id="expense-reference"
                                   type="text"
                                   value="DEP-AG-2026-0001"
                                   disabled>
                        </label>

                        <label class="form-field">
                            <span>Libellé</span>
                            <input id="expense-label"
                                   type="text"
                                   placeholder="Ex : Achat ramettes papier A4">
                        </label>

                        <label class="form-field">
                            <span>Montant</span>
                            <input id="expense-amount"
                                   type="number"
                                   min="0"
                                   placeholder="Ex : 4500">
                        </label>

                        <label class="form-field">
                            <span>Mode de paiement</span>
                            <select id="expense-payment-mode">
                                <option>Espèces</option>
                                <option>Wave</option>
                                <option>Orange Money</option>
                                <option>Virement bancaire</option>
                            </select>
                        </label>

                        <label class="form-field">
                            <span>Justificatif</span>
                            <select id="expense-proof-type">
                                <option value="Aucun">Aucun</option>
                                <option value="Reçu">Reçu</option>
                                <option value="Facture">Facture</option>
                                <option value="Bon de sortie">Bon de sortie</option>
                            </select>
                        </label>

                        <label class="form-field form-field-wide">
                            <span>Description / observation</span>
                            <textarea id="expense-observation"
                                      placeholder="Observation facultative..."></textarea>
                        </label>

                    </div>

                    <div class="agency-expense-actions">
                        <button type="button" class="btn btn-outline" id="reset-current-expense">
                            Annuler
                        </button>

                        <button type="button" class="btn btn-primary" id="add-expense">
                            Ajouter la dépense
                        </button>
                    </div>
                </form>
            </div>

            <div class="agency-expense-list-card">
                <div class="agency-expense-list-header">
                    <div>
                        <h3>Dépenses à payer</h3>
                        <p id="expense-count">Aucune dépense ajoutée</p>
                    </div>

                    <strong id="expense-total" class="is-danger">0 FCFA</strong>
                </div>

                <div id="expense-empty" class="expense-empty">
                    Ajoutez une ou plusieurs dépenses avant de valider le décaissement.
                </div>

                <div id="expense-list" class="expense-list"></div>

                <div class="agency-expense-final-actions">
                    <button type="button" class="btn btn-outline" id="clear-expenses">
                        Tout vider
                    </button>

                    <button type="button" class="btn btn-primary" id="validate-expenses" disabled>
                        Valider
                    </button>
                </div>
            </div>

        </div>

    </section>

    <style>
        .agency-expense-wrapper {
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(320px, 1fr);
            gap: 1.5rem;

            width: 100%;
            max-width: 1400px;

            margin: 2rem auto 0;
            align-items: start;
        }

        .agency-expense-card,
        .agency-expense-list-card {
            min-width: 0;
        }

        .expense-row {
            display: flex;
            flex-direction: column;
            gap: .85rem;

            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: var(--surface-subtle);
        }

        .expense-row-top {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            align-items: flex-start;
        }

        .expense-row h4 {
            margin: 0;
            font-size: .95rem;
            line-height: 1.3;
            word-break: break-word;
        }

        .expense-row p {
            margin: .3rem 0 0;
            color: var(--muted-foreground);
            font-size: .82rem;
            line-height: 1.35;
            word-break: break-word;
        }

        .expense-row strong {
            white-space: nowrap;
            font-size: 1rem;
        }

        .expense-row-footer {
            display: flex;
            justify-content: flex-end;
            padding-top: .35rem;
            border-top: 1px solid var(--border);
        }

        .expense-remove {
            border: none;
            background: transparent;
            color: #dc2626;
            cursor: pointer;
            font-weight: 700;
            padding: 0;
        }

        .expense-row-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: .5rem;
        }

        .agency-expense-card {
            height: fit-content;
        }

        .agency-expense-list-card {
            position: sticky;
            top: 1rem;
            height: fit-content;
        }

        @media (max-width: 992px) {

            .agency-expense-wrapper {
                grid-template-columns: 1fr;
            }

            .agency-expense-list-card {
                position: static;
            }

        }

        .agency-expense-card,
        .agency-expense-list-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
        }

        .agency-expense-card,
        .agency-expense-list-card {
            padding: 1.5rem;
        }

        .agency-expense-list-card {
            position: sticky;
            top: 1rem;
            height: fit-content;
            margin-top: 0;
        }

        .agency-expense-card-header,
        .agency-expense-list-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }

        .agency-expense-card-header h3,
        .agency-expense-list-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .agency-expense-card-header p,
        .agency-expense-list-header p {
            margin: .35rem 0 0;
            color: var(--muted-foreground);
        }

        .agency-expense-actions,
        .agency-expense-final-actions {
            display: flex;
            justify-content: flex-end;
            gap: .75rem;
            margin-top: 1.5rem;
        }

        .expense-empty {
            padding: 1rem;
            border: 1px dashed var(--border);
            border-radius: 1rem;
            color: var(--muted-foreground);
            background: var(--surface-subtle);
            text-align: center;
        }

        .expense-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .expense-row {
            display: flex;
            flex-direction: column;
            gap: .35rem;

            padding: 1rem;

            border: 1px solid var(--border);
            border-radius: 1rem;

            background: var(--surface-subtle);
        }

        .expense-row h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .expense-row p {
            margin: 0;
            font-size: .85rem;
            color: var(--muted-foreground);
            line-height: 1.4;
        }

        .expense-row-footer {
            margin-top: .5rem;
            padding-top: .75rem;

            border-top: 1px solid var(--border);

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .expense-row strong {
            font-size: 1rem;
            font-weight: 700;
        }

        .expense-remove {
            border: none;
            background: transparent;
            color: #dc2626;
            cursor: pointer;
            font-weight: 700;
        }


        .is-danger {
            color: #dc2626 !important;
        }

        @media (max-width: 768px) {
            .agency-expense-card-header,
            .agency-expense-list-header,
            .agency-expense-actions,
            .agency-expense-final-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .expense-row {
                grid-template-columns: 1fr;
            }
        }

        .payment-shortcuts{
            width:1100px;
            max-width:100%;
            margin:1rem auto 1.5rem;
            display:grid;
            grid-template-columns:repeat(4,minmax(0,1fr));
            gap:.75rem;
        }

        .payment-shortcut{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.65rem;

            padding:1rem;
            min-height:60px;

            border-radius:1rem;
            text-decoration:none;

            background:var(--card);
            border:1px solid var(--border);

            color:var(--foreground);
            font-weight:600;

            transition:.2s ease;
        }

        .payment-shortcut svg{
            width:20px;
            height:20px;
            flex-shrink:0;
        }

        .payment-shortcut:hover{
            border-color:var(--primary);
            color:var(--primary);
            transform:translateY(-1px);
        }

        .payment-shortcut.active{
            background:rgba(37,99,235,.12);
            border-color:var(--primary);
            color:var(--primary);
        }

        @media (max-width:900px){
            .payment-shortcuts{
                grid-template-columns:1fr 1fr;
            }
        }

        @media (max-width:600px){
            .payment-shortcuts{
                grid-template-columns:1fr;
            }
        }
    </style>

    <script>
        const expenseCategory = document.getElementById('expense-category');
        const expenseReference = document.getElementById('expense-reference');
        const expenseLabel = document.getElementById('expense-label');
        const expenseAmount = document.getElementById('expense-amount');
        const expensePaymentMode = document.getElementById('expense-payment-mode');
        const expenseProofType = document.getElementById('expense-proof-type');
        const expenseObservation = document.getElementById('expense-observation');

        const addExpenseBtn = document.getElementById('add-expense');
        const resetCurrentExpenseBtn = document.getElementById('reset-current-expense');
        const clearExpensesBtn = document.getElementById('clear-expenses');
        const validateExpensesBtn = document.getElementById('validate-expenses');

        const expenseList = document.getElementById('expense-list');
        const expenseEmpty = document.getElementById('expense-empty');
        const expenseTotal = document.getElementById('expense-total');
        const expenseCount = document.getElementById('expense-count');

        let expenses = [];
        let referenceCounter = 1;

        function formatAmount(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
        }

        function generateReference() {
            return `DEP-AG-2026-${String(referenceCounter).padStart(4, '0')}`;
        }

        function resetCurrentExpense() {
            expenseCategory.value = '';
            expenseLabel.value = '';
            expenseAmount.value = '';
            expensePaymentMode.value = 'Espèces';
            expenseProofType.value = 'Aucun';
            expenseObservation.value = '';
            expenseReference.value = generateReference();
        }

        function renderExpenses() {
            expenseList.innerHTML = '';

            const total = expenses.reduce((sum, item) => sum + item.amount, 0);

            expenseTotal.textContent = formatAmount(total);
            expenseCount.textContent = expenses.length
                ? `${expenses.length} dépense(s) ajoutée(s)`
                : 'Aucune dépense ajoutée';

            expenseEmpty.style.display = expenses.length ? 'none' : 'block';
            validateExpensesBtn.disabled = expenses.length === 0;

            expenses.forEach((item, index) => {
                expenseList.innerHTML += `
        <div class="expense-row">

            <h4>${item.label}</h4>

            <p>${item.reference}</p>

            <p>
                ${item.category} ·
                ${item.paymentMode} ·
                ${item.proofType}
            </p>

            ${item.observation
                    ? `<p>${item.observation}</p>`
                    : ''}

            <div class="expense-row-footer">
                <strong class="is-danger">
                    ${formatAmount(item.amount)}
                </strong>

                <button type="button"
                        class="expense-remove"
                        onclick="removeExpense(${index})">
                    Supprimer
                </button>
            </div>

        </div>
    `;
            });
        }

        function removeExpense(index) {
            expenses.splice(index, 1);
            renderExpenses();
        }

        addExpenseBtn?.addEventListener('click', function () {
            const category = expenseCategory.value;
            const label = expenseLabel.value.trim();
            const amount = Number(expenseAmount.value || 0);

            if (!category) {
                alert('Veuillez sélectionner une catégorie.');
                return;
            }

            if (!label) {
                alert('Veuillez renseigner le libellé.');
                return;
            }

            if (amount <= 0) {
                alert('Veuillez renseigner un montant valide.');
                return;
            }

            expenses.push({
                reference: expenseReference.value,
                category: category,
                label: label,
                amount: amount,
                paymentMode: expensePaymentMode.value,
                proofType: expenseProofType.value,
                observation: expenseObservation.value.trim()
            });

            referenceCounter++;
            resetCurrentExpense();
            renderExpenses();
        });

        resetCurrentExpenseBtn?.addEventListener('click', resetCurrentExpense);

        clearExpensesBtn?.addEventListener('click', function () {
            if (!expenses.length) return;

            if (confirm('Voulez-vous vraiment vider toutes les dépenses ajoutées ?')) {
                expenses = [];
                referenceCounter = 1;
                resetCurrentExpense();
                renderExpenses();
            }
        });

        validateExpensesBtn?.addEventListener('click', function () {
            if (!expenses.length) return;

            const total = expenses.reduce((sum, item) => sum + item.amount, 0);

            if (confirm(`Valider le décaissement global de ${formatAmount(total)} ?`)) {
                alert('Dépenses validées en statique.');

                expenses = [];
                referenceCounter = 1;
                resetCurrentExpense();
                renderExpenses();
            }
        });

        resetCurrentExpense();
        renderExpenses();
    </script>

@endsection