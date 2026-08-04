import React from 'react';

export default function Stats({ totalAttendu, totalPaye, totalRestant, fmt }) {
    return (
        <div className="stats">
            <div className="stat-card blue">
                <div>
                    <div className="stat-label">Total attendu</div>
                    <div className="stat-value">{fmt(totalAttendu)}</div>
                </div>
                <div className="stat-icon">📅</div>
            </div>
            <div className="stat-card green">
                <div>
                    <div className="stat-label">Total payé</div>
                    <div className="stat-value">{fmt(totalPaye)}</div>
                </div>
                <div className="stat-icon">✓</div>
            </div>
            <div className="stat-card red">
                <div>
                    <div className="stat-label">Total restant</div>
                    <div className="stat-value">{fmt(totalRestant)}</div>
                </div>
                <div className="stat-icon">⚠</div>
            </div>
        </div>
    );
}