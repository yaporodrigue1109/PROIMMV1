import React from 'react';
import { Document, Page, View, Text, StyleSheet, Image, Font } from '@react-pdf/renderer';

// Enregistrement des polices pour un meilleur rendu
Font.register({
    family: 'Helvetica',
    fonts: [
        { src: 'https://fonts.gstatic.com/s/opensans/v40/memvYaGs126MiZpBA-UvWbX2vVnXBbObj2OVTS-mu0SC55I.woff2' },
    ]
});

// ============================================================
// Convertisseur nombre → lettres (français)
// ============================================================
const UNITES = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix',
    'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
const DIZAINES = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];

function centainesEnLettres(n) {
    let mots = '';
    const c = Math.floor(n / 100);
    const reste = n % 100;
    if (c > 0) {
        mots += (c > 1 ? UNITES[c] + ' cent' : 'cent') + (c > 1 && reste === 0 ? 's' : '');
        if (reste > 0) mots += ' ';
    }
    if (reste > 0) {
        if (reste < 20) {
            mots += UNITES[reste];
        } else {
            const d = Math.floor(reste / 10);
            const u = reste % 10;
            if (d === 7 || d === 9) {
                mots += DIZAINES[d - 1] + '-' + UNITES[10 + u];
            } else {
                mots += DIZAINES[d] + (u > 0 ? (u === 1 && d !== 8 ? ' et un' : '-' + UNITES[u]) : (d === 8 ? 's' : ''));
            }
        }
    }
    return mots;
}

function nombreEnLettres(n) {
    n = Math.round(Number(n) || 0);
    if (n === 0) return 'zéro';
    if (n < 0) return 'moins ' + nombreEnLettres(-n);
    const millions = Math.floor(n / 1000000);
    const milliers = Math.floor((n % 1000000) / 1000);
    const unites = n % 1000;
    let mots = [];
    if (millions > 0) mots.push((millions > 1 ? centainesEnLettres(millions) + ' millions' : 'un million'));
    if (milliers > 0) mots.push((milliers > 1 ? centainesEnLettres(milliers) + ' mille' : 'mille'));
    if (unites > 0) mots.push(centainesEnLettres(unites));
    return mots.join(' ').trim();
}

const montantEnLettres = (n) => {
    const mots = nombreEnLettres(n);
    return mots.charAt(0).toUpperCase() + mots.slice(1) + ' francs CFA';
};

const fmt = (v) => {
    const n = Math.round(Number(v ?? 0));
    const sign = n < 0 ? '-' : '';
    const digits = Math.abs(n).toString();
    const withDots = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return sign + withDots;
};

const fmtDate = (iso) => {
    if (!iso) return '—';
    const date = new Date(iso);
    if (isNaN(date.getTime())) return '—';
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    return `${day}/${month}/${year}`;
};

const today = () => {
    const date = new Date();
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

// ============================================================
// Colonnes de la fiche
// ============================================================
const COLUMNS = [
    { key: 'porte', label: 'N° Porte', w: 4, align: 'left' },
    { key: 'nom', label: 'Nom et prénom des locataires', w: 11, align: 'left' },
    { key: 'dateEntree', label: "Date d'entrée", w: 6, align: 'center', group: 'situation' },
    { key: 'cautionPayee', label: 'Caution payée', w: 6, align: 'right', group: 'situation' },
    { key: 'datePaiement', label: 'Date de paiement', w: 6, align: 'center' },
    { key: 'montantLoyer', label: 'Montant du loyer', w: 6, align: 'right' },
    { key: 'arrieres', label: 'Arriérés', w: 5, align: 'right' },
    { key: 'montantAttendu', label: 'Montant attendu', w: 6, align: 'right', bold: true },
    { key: 'moisPayer', label: 'Nom du mois', w: 6, align: 'center', group: 'avance' },
    { key: 'avance', label: 'Montant payé', w: 5, align: 'right', group: 'avance' },
    { key: 'nouvelleCaution', label: 'Nouvelle caution', w: 5, align: 'right' },
    { key: 'loyerPaye', label: 'Loyer payé', w: 5, align: 'right' },
    { key: 'arrierePaye', label: 'Arriéré payé', w: 5, align: 'right' },
    { key: 'cautionSodeci', label: 'Caution SODECI/CIE', w: 6, align: 'right' },
    { key: 'totalPaye', label: 'Total payé', w: 5, align: 'right', green: true },
    { key: 'restant', label: 'Impayés', w: 5, align: 'right', red: true },
    { key: 'tel', label: 'Numéro de tel', w: 6, align: 'left' },
    { key: 'numeroRecu', label: 'N° reçu', w: 4, align: 'center' },
];
const TOTAL_WEIGHT = COLUMNS.reduce((s, c) => s + c.w, 0);
const widthPct = (w) => `${(w / TOTAL_WEIGHT) * 100}%`;

const FOOTER_MERGE_START = 5;
const FOOTER_EMPTY_END = 2;

const styles = StyleSheet.create({
    page: { 
        padding: 20, 
        fontSize: 8, 
        fontFamily: 'Helvetica', 
        color: '#0f172a',
        backgroundColor: '#ffffff',
    },
    watermark: {
        position: 'absolute',
        top: '25%',
        left: '32%',
        width: '36%',
        height: '50%',
        objectFit: 'contain',
        opacity: 0.07,
    },

    // Header amélioré
    headerRow: { 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        marginBottom: 15,
        borderBottomWidth: 2,
        borderBottomColor: '#00559b',
        paddingBottom: 12,
    },
    logoBox: {
        width: 50, 
        height: 50, 
        borderRadius: 8, 
        backgroundColor: '#00559b',
        alignItems: 'center', 
        justifyContent: 'center', 
        overflow: 'hidden',
    },
    logoImg: { width: 50, height: 50 },
    logoText: { color: '#ffffff', fontSize: 16, fontWeight: 'bold' },
    entrepriseName: { fontSize: 8, color: '#5f7182', marginTop: 4, maxWidth: 100, textAlign: 'center' },
    
    titleBlock: { flex: 1, alignItems: 'center', paddingHorizontal: 10 },
    title: { fontSize: 16, fontWeight: 'bold', color: '#00559b', marginBottom: 4 },
    subLine: { fontSize: 9, marginTop: 2, color: '#1e293b' },
    subLineBold: { fontSize: 9, fontWeight: 'bold', color: '#0f172a' },
    
    periodeBox: { 
        borderWidth: 2, 
        borderColor: '#00559b', 
        borderRadius: 8, 
        paddingVertical: 6, 
        paddingHorizontal: 12, 
        alignItems: 'center', 
        minWidth: 140,
        backgroundColor: '#f8fafc',
    },
    periodeLabel: { fontSize: 7, fontWeight: 'bold', color: '#00559b' },
    periodeValue: { fontSize: 9, fontWeight: 'bold', color: '#0f172a', marginTop: 2 },

    // Tableau amélioré
    table: { 
        borderTopWidth: 1, 
        borderLeftWidth: 1, 
        borderColor: '#cbd5e1', 
        marginBottom: 12,
        borderRadius: 4,
        overflow: 'hidden',
    },
    
    groupBarRow: { flexDirection: 'row' },
    groupBarCell: {
        borderRightWidth: 1, 
        borderBottomWidth: 1, 
        borderColor: '#cbd5e1',
        paddingVertical: 3, 
        justifyContent: 'center', 
        alignItems: 'center',
        backgroundColor: '#e8f0fe',
    },
    groupBarLabel: { 
        fontSize: 7, 
        fontWeight: 'bold', 
        color: '#00559b',
        textAlign: 'center',
        paddingVertical: 2,
    },
    
    headRow: { flexDirection: 'row', backgroundColor: '#f1f5f9' },
    headCell: {
        borderRightWidth: 1, 
        borderBottomWidth: 1, 
        borderColor: '#cbd5e1',
        padding: 3, 
        fontSize: 6.5, 
        fontWeight: 'bold', 
        color: '#0f172a',
        textAlign: 'center', 
        justifyContent: 'center',
        backgroundColor: '#f8fafc',
    },
    
    bodyRow: { flexDirection: 'row' },
    bodyRowAlt: { backgroundColor: '#fafcfd' },
    bodyCell: {
        borderRightWidth: 1, 
        borderBottomWidth: 1, 
        borderColor: '#e2e8f0',
        padding: 3, 
        fontSize: 7, 
        justifyContent: 'center',
    },
    
    footRow: { flexDirection: 'row', backgroundColor: '#eef2f6' },
    footCell: {
        borderRightWidth: 1, 
        borderBottomWidth: 1, 
        borderColor: '#cbd5e1',
        padding: 3, 
        fontSize: 7, 
        fontWeight: 'bold', 
        color: '#0f172a',
        justifyContent: 'center',
    },

    green: { color: '#4d8500', fontWeight: 'bold' },
    red: { color: '#dc2626', fontWeight: 'bold' },

    // Récapitulatif amélioré
    summaryRow: { flexDirection: 'row', marginTop: 12, gap: 12 },
    recapBox: { 
        width: '50%', 
        borderWidth: 1, 
        borderColor: '#cbd5e1', 
        borderRadius: 8,
        overflow: 'hidden',
        backgroundColor: '#f8fafc',
    },
    recapLine: { 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        borderBottomWidth: 1, 
        borderColor: '#e2e8f0', 
        paddingVertical: 5, 
        paddingHorizontal: 10,
    },
    recapLineLast: { 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        backgroundColor: '#00559b', 
        paddingVertical: 6, 
        paddingHorizontal: 10,
        borderBottomLeftRadius: 8,
        borderBottomRightRadius: 8,
    },
    recapLabel: { fontSize: 8.5, color: '#1e293b' },
    recapValue: { fontSize: 8.5, fontWeight: 'bold', color: '#0f172a' },
    recapValueLast: { fontSize: 10, fontWeight: 'bold', color: '#ffffff' },
    recapLabelLast: { fontSize: 9, fontWeight: 'bold', color: '#ffffff' },
    
    obsBox: { 
        width: '50%', 
        borderWidth: 1, 
        borderColor: '#cbd5e1', 
        borderRadius: 8, 
        padding: 10, 
        minHeight: 90,
        backgroundColor: '#f8fafc',
    },
    obsTitle: { fontSize: 8.5, fontWeight: 'bold', color: '#00559b', marginBottom: 6 },
    obsText: { fontSize: 8.5, color: '#1e293b', lineHeight: 1.4 },

    // Signature améliorée
    mention: { 
        textAlign: 'center', 
        fontSize: 10, 
        fontWeight: 'bold', 
        color: '#0f172a',
        marginTop: 16,
        paddingTop: 12,
        borderTopWidth: 1,
        borderTopColor: '#e2e8f0',
    },
    
    signatureRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginTop: 12,
        paddingHorizontal: 20,
    },
    signatureBox: {
        alignItems: 'center',
        gap: 4,
    },
    signatureLine: {
        width: 120,
        borderBottomWidth: 1,
        borderBottomColor: '#0f172a',
        marginTop: 20,
        marginBottom: 4,
    },
    signatureLabel: {
        fontSize: 8,
        color: '#5f7182',
    },
    
    footerDate: { 
        fontSize: 8, 
        color: '#5f7182',
        marginTop: 8,
    },
});

function cellValue(col, l) {
    switch (col.key) {
        case 'porte': return l.porte ?? '';
        case 'nom': return l.nom ?? '';
        case 'dateEntree': return fmtDate(l.dateEntree);
        case 'cautionPayee': return fmt(l.cautionPayee);
        case 'datePaiement': return l.datePaiement ? fmtDate(l.datePaiement) : '-';
        case 'montantLoyer': return fmt(l.montantLoyer);
        case 'arrieres': return fmt(l.arrieres);
        case 'montantAttendu': return fmt(l.montantAttendu);
        case 'moisPayer': return Array.isArray(l.mois_payer) ? l.mois_payer.join(' , ') : (l.mois_payer || '-');
        case 'avance': return fmt(l.avance);
        case 'nouvelleCaution': return fmt(l.nouvelleCaution);
        case 'loyerPaye': return fmt(l.loyerPaye);
        case 'arrierePaye': return fmt(l.arrierePaye);
        case 'cautionSodeci': return fmt(l.cautionSodeci);
        case 'totalPaye': return fmt(l.totalPaye);
        case 'restant': return fmt(l.restant);
        case 'tel': return l.tel ?? '';
        case 'numeroRecu': return l.numeroRecu || '-';
        default: return '';
    }
}

export default function ReversementPdfDocument({ cour }) {
    const locataires = cour?.locataires || [];
    const totaux = cour?.totaux || {};

    const sums = {
        montantLoyer: locataires.reduce((s, l) => s + (l.montantLoyer || 0), 0),
        arrieres: locataires.reduce((s, l) => s + (l.arrieres || 0), 0),
        montantAttendu: locataires.reduce((s, l) => s + (l.montantAttendu || 0), 0),
        avance: locataires.reduce((s, l) => s + (l.avance || 0), 0),
        nouvelleCaution: locataires.reduce((s, l) => s + (l.nouvelleCaution || 0), 0),
        loyerPaye: locataires.reduce((s, l) => s + (l.loyerPaye || 0), 0),
        arrierePaye: locataires.reduce((s, l) => s + (l.arrierePaye || 0), 0),
        cautionSodeci: locataires.reduce((s, l) => s + (l.cautionSodeci || 0), 0),
        totalPaye: locataires.reduce((s, l) => s + (l.totalPaye || 0), 0),
        restant: locataires.reduce((s, l) => s + (l.restant || 0), 0),
    };

    const net = Number(totaux.net ?? 0);
    const mergedWidth = COLUMNS.slice(0, FOOTER_MERGE_START).reduce((s, c) => s + c.w, 0);
    const emptyWidth = COLUMNS.slice(COLUMNS.length - FOOTER_EMPTY_END).reduce((s, c) => s + c.w, 0);
    const footerCols = COLUMNS.slice(FOOTER_MERGE_START, COLUMNS.length - FOOTER_EMPTY_END);
    const footerValues = {
        montantLoyer: sums.montantLoyer, arrieres: sums.arrieres, montantAttendu: sums.montantAttendu,
        moisPayer: null, avance: sums.avance, nouvelleCaution: sums.nouvelleCaution,
        loyerPaye: sums.loyerPaye, arrierePaye: sums.arrierePaye, cautionSodeci: sums.cautionSodeci,
        totalPaye: sums.totalPaye, restant: sums.restant,
    };

    return (
        <Document>
            <Page size="A4" orientation="landscape" style={styles.page}>
                {cour?.logo_entreprise ? (
                    <Image src={cour.logo_entreprise} style={styles.watermark} fixed />
                ) : null}
                {/* En-tête amélioré */}
                <View style={styles.headerRow}>
                    <View>
                        <View style={styles.logoBox}>
                            {cour?.logo_entreprise ? 
                                <Image src={cour.logo_entreprise} style={styles.logoImg} /> : 
                                <Text style={styles.logoText}>LOGO</Text>
                            }
                        </View>
                      
                    </View>
                    <View style={styles.titleBlock}>
                        <Text style={styles.title}>Fiche d'encaissement de loyers</Text>
                          <Text style={styles.entrepriseName}>{cour?.name_entreprise || ''}</Text>
                        <Text style={styles.subLine}>
                            Nom du bailleur : <Text style={styles.subLineBold}>{cour?.proprietaire_nom || '—'}</Text>
                        </Text>
                        <Text style={styles.subLine}>
                            Cours : <Text style={styles.subLineBold}>{cour?.nom || '—'}</Text>
                        </Text>
                    </View>
                    <View style={styles.periodeBox}>
                        <Text style={styles.periodeLabel}>Période</Text>
                        <Text style={styles.periodeValue}>
                            {fmtDate(cour?.periode?.debut)} - {fmtDate(cour?.periode?.fin)}
                        </Text>
                    </View>
                </View>

                {/* Tableau */}
                <View style={styles.table}>
                    {/* Barre de regroupement */}
                    <View style={styles.groupBarRow}>
                        {COLUMNS.map((col) => {
                            if (col.group === 'situation' && col.key === 'dateEntree') {
                                return (
                                    <View key="grp-situation" style={[styles.groupBarCell, { width: widthPct(col.w + COLUMNS[3].w) }]}>
                                        <Text style={styles.groupBarLabel}>Situation des locataires</Text>
                                    </View>
                                );
                            }
                            if (col.group === 'avance' && col.key === 'moisPayer') {
                                return (
                                    <View key="grp-avance" style={[styles.groupBarCell, { width: widthPct(col.w + COLUMNS[9].w) }]}>
                                        <Text style={styles.groupBarLabel}>Loyer payé en avance</Text>
                                    </View>
                                );
                            }
                            if (col.key === 'cautionPayee' || col.key === 'avance') return null;
                            return <View key={`grp-${col.key}`} style={{ width: widthPct(col.w) }} />;
                        })}
                    </View>

                    {/* En-têtes */}
                    <View style={styles.headRow}>
                        {COLUMNS.map((col) => (
                            <View key={col.key} style={[styles.headCell, { width: widthPct(col.w) }]}>
                                <Text>{col.label}</Text>
                            </View>
                        ))}
                    </View>

                    {/* Lignes locataires */}
                    {locataires.map((l, idx) => (
                        <View key={`${l.porte_id}-${l.locataire_id}-${idx}`} style={[styles.bodyRow, idx % 2 === 1 ? styles.bodyRowAlt : null]}>
                            {COLUMNS.map((col) => (
                                <View key={col.key} style={[styles.bodyCell, { width: widthPct(col.w) }]}>
                                    <Text
                                        style={[
                                            col.align === 'right' ? { textAlign: 'right' } : 
                                            col.align === 'center' ? { textAlign: 'center' } : null,
                                            col.bold ? { fontWeight: 'bold' } : null,
                                            col.green ? styles.green : null,
                                            col.red && (l.restant || 0) > 0 ? styles.red : null,
                                        ]}
                                    >
                                        {cellValue(col, l)}
                                    </Text>
                                </View>
                            ))}
                        </View>
                    ))}

                    {/* Ligne TOTAUX */}
                    <View style={styles.footRow}>
                        <View style={[styles.footCell, { width: widthPct(mergedWidth) }]}>
                            <Text>TOTAUX</Text>
                        </View>
                        {footerCols.map((col) => (
                            <View key={`foot-${col.key}`} style={[styles.footCell, { width: widthPct(col.w) }]}>
                                <Text style={{ textAlign: col.key === 'moisPayer' ? 'center' : 'right' }}>
                                    {footerValues[col.key] === null ? '' : fmt(footerValues[col.key])}
                                </Text>
                            </View>
                        ))}
                        <View style={[styles.footCell, { width: widthPct(emptyWidth) }]}>
                            <Text></Text>
                        </View>
                    </View>
                </View>

                {/* Récapitulatif */}
                <View style={styles.summaryRow}>
                    <View style={styles.recapBox}>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>TOTAL LOYER ENCAISSÉ</Text>
                            <Text style={styles.recapValue}>{fmt(totaux.totalEncaisse)} FCFA</Text>
                        </View>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>
                                COMMISSION ({Math.round((cour?.commissionRate || 0.10) * 100)}%)
                            </Text>
                            <Text style={styles.recapValue}>{fmt(totaux.commission)} FCFA</Text>
                        </View>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>MONTANT APRÈS COMMISSION</Text>
                            <Text style={styles.recapValue}>{fmt(totaux.apresCommission)} FCFA</Text>
                        </View>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>NOUVELLE CAUTION</Text>
                            <Text style={styles.recapValue}>{fmt(cour?.nouvelleCaution)} FCFA</Text>
                        </View>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>Caution cie/ Sodeci</Text>
                            <Text style={styles.recapValue}>{fmt(cour?.cautionSodeci)} FCFA</Text>
                        </View>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>MAINTENANCES — MONTANT VERSÉ SUR LA PÉRIODE</Text>
                            <Text style={styles.recapValue}>{fmt(cour?.montantMaintenances)} FCFA</Text>
                        </View>
                        <View style={styles.recapLine}>
                            <Text style={styles.recapLabel}>DÉPENSES EFFECTUÉES</Text>
                            <Text style={styles.recapValue}>{fmt(cour?.depenses)} FCFA</Text>
                        </View>
                        <View style={styles.recapLineLast}>
                            <Text style={styles.recapLabelLast}>NET À REVERSER</Text>
                            <Text style={styles.recapValueLast}>{fmt(net)} FCFA</Text>
                        </View>
                    </View>

                    <View style={styles.obsBox}>
                        {/* <Text style={styles.obsTitle}>📝 Observation</Text> */}
                        <Text style={styles.obsText}>{cour?.observation || 'Aucune observation'}</Text>
                    </View>
                </View>

                {/* Mention légale */}
                <Text style={styles.mention}>
                    Je reconnais avoir reçu la somme de : {montantEnLettres(net)}
                </Text>

                {/* Signatures */}
                <View style={styles.signatureRow}>
                    <View style={styles.signatureBox}>
                        <View style={styles.signatureLine} />
                        <Text style={styles.signatureLabel}>LE GESTIONNAIRE</Text>
                    </View>
                    <View style={styles.signatureBox}>
                        <View style={styles.signatureLine} />
                        <Text style={styles.signatureLabel}>LE BAILLEUR</Text>
                    </View>
                </View>

                {/* Pied de page */}
                {/* <Text style={styles.footerDate}>
                    Fait le {today()}
                </Text> */}
            </Page>
        </Document>
    );
}
