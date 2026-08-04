import React from 'react';

export default function FicheDetails({
    cour,
    t,
    readonly,
    fmt,
    onLocataireChange,
    onCourFieldChange,
    today
}) {
    return (
        <>
            <div className="table-scroll" style={{ padding: '0 24px' }}>
                <table id="fiche-table">
                    <thead>
                        <tr>
                            <th>N° Porte</th>
                            <th>Locataire</th>
                            <th>Téléphone</th>
                            <th>Montant loyer</th>
                            <th>Arriérés</th>
                            <th>Montant attendu</th>
                            <th>Loyer payé</th>
                            <th>Arriéré payé</th>
                            <th>Total payé</th>
                            <th>Impayés</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cour.locataires.map((l, idx) => {
                            const attendu = l.montantLoyer + l.arrieresInit;
                            const totalPaye = l.loyerPaye + l.arrierePaye;
                            const impayes = attendu - totalPaye;
                            return (
                                <tr key={idx}>
                                    <td>{l.porte}</td>
                                    <td>{l.nom}</td>
                                    <td>{l.tel}</td>
                                    <td>{fmt(l.montantLoyer)}</td>
                                    <td>{fmt(l.arrieresInit)}</td>
                                    <td className="amount-blue">{fmt(attendu)}</td>
                                    <td>
                                        <input
                                            className="cell-input"
                                            type="number"
                                            min="0"
                                            value={l.loyerPaye}
                                            disabled={readonly}
                                            onChange={(e) => onLocataireChange(idx, 'loyerPaye', e.target.value)}
                                        />
                                    </td>
                                    <td>
                                        <input
                                            className="cell-input"
                                            type="number"
                                            min="0"
                                            value={l.arrierePaye}
                                            disabled={readonly}
                                            onChange={(e) => onLocataireChange(idx, 'arrierePaye', e.target.value)}
                                        />
                                    </td>
                                    <td className="amount-green">{fmt(totalPaye)}</td>
                                    <td className={impayes > 0 ? 'amount-red' : 'amount-green'}>
                                        {fmt(impayes)}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                    <tfoot>
                        <tr className="totals-row">
                            <td colSpan="3">TOTAUX</td>
                            <td>{fmt(t.montantLoyer)}</td>
                            <td>{fmt(t.arrieres)}</td>
                            <td>{fmt(t.attendu)}</td>
                            <td>{fmt(t.loyerPaye)}</td>
                            <td>{fmt(t.arrierePaye)}</td>
                            <td>{fmt(t.totalPaye)}</td>
                            <td>{fmt(t.restant)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div className="calc-block">
                <table className="calc-table">
                    <tbody>
                        <tr>
                            <td>Total loyer encaissé</td>
                            <td>{fmt(t.totalPaye)}</td>
                        </tr>
                        <tr>
                            <td>Commission BAZ ({Math.round(cour.commissionRate * 100)}%)</td>
                            <td>{fmt(t.commission)}</td>
                        </tr>
                        <tr>
                            <td>Montant après commission</td>
                            <td>{fmt(t.apresCommission)}</td>
                        </tr>
                        <tr>
                            <td>Nouvelle caution</td>
                            <td>
                                <input
                                    className="cell-input"
                                    style={{ width: '120px', textAlign: 'right' }}
                                    type="number"
                                    min="0"
                                    value={cour.nouvelleCaution}
                                    disabled={readonly}
                                    onChange={(e) => onCourFieldChange('nouvelleCaution', e.target.value)}
                                />
                            </td>
                        </tr>
                        <tr>
                            <td>Dépenses effectuées</td>
                            <td>
                                <input
                                    className="cell-input"
                                    style={{ width: '120px', textAlign: 'right' }}
                                    type="number"
                                    min="0"
                                    value={cour.depenses}
                                    disabled={readonly}
                                    onChange={(e) => onCourFieldChange('depenses', e.target.value)}
                                />
                            </td>
                        </tr>
                        <tr className="net">
                            <td>NET À REVERSER</td>
                            <td>{fmt(t.net)}</td>
                        </tr>
                    </tbody>
                </table>

                <div className="obs-box">
                    <label>Observation</label>
                    <textarea
                        disabled={readonly}
                        value={cour.observation}
                        onChange={(e) => onCourFieldChange('observation', e.target.value)}
                    />
                </div>
            </div>

            <div className="signature-line">
                <div>Fait à Anyama carrefour Berthé, le <b>{today()}</b></div>
            </div>
            <div className="signature-line">
                <div><b>LE GESTIONNAIRE</b></div>
                <div><b>LE BAILLEUR</b></div>
            </div>
        </>
    );
}