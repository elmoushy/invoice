import React, { useState } from 'react';

const CountryCode = ({ value, onChange, className = '' }) => {
    const [selectedCode, setSelectedCode] = useState(value || '');

    const countryCodes = [
        { code: 'US', name: 'United States', flag: '🇺🇸' },
        { code: 'CA', name: 'Canada', flag: '🇨🇦' },
        { code: 'GB', name: 'United Kingdom', flag: '🇬🇧' },
        { code: 'DE', name: 'Germany', flag: '🇩🇪' },
        { code: 'FR', name: 'France', flag: '🇫🇷' },
        { code: 'IT', name: 'Italy', flag: '🇮🇹' },
        { code: 'ES', name: 'Spain', flag: '🇪🇸' },
        { code: 'AU', name: 'Australia', flag: '🇦🇺' },
        { code: 'JP', name: 'Japan', flag: '🇯🇵' },
        { code: 'CN', name: 'China', flag: '🇨🇳' },
        { code: 'IN', name: 'India', flag: '🇮🇳' },
        { code: 'BR', name: 'Brazil', flag: '🇧🇷' },
        { code: 'MX', name: 'Mexico', flag: '🇲🇽' },
        { code: 'RU', name: 'Russia', flag: '🇷🇺' },
        { code: 'ZA', name: 'South Africa', flag: '🇿🇦' },
        { code: 'AE', name: 'United Arab Emirates', flag: '🇦🇪' },
        { code: 'SA', name: 'Saudi Arabia', flag: '🇸🇦' },
        { code: 'EG', name: 'Egypt', flag: '🇪🇬' }
    ];

    const handleChange = (e) => {
        const newValue = e.target.value;
        setSelectedCode(newValue);
        if (onChange) {
            onChange(newValue);
        }
    };

    return (
        <div className={`country-code-selector ${className}`}>
            <label htmlFor="country-code" className="block text-sm font-medium mb-1">
                Country Code
            </label>
            <select
                id="country-code"
                value={selectedCode}
                onChange={handleChange}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Select Country</option>
                {countryCodes.map((country) => (
                    <option key={country.code} value={country.code}>
                        {country.flag} {country.code} - {country.name}
                    </option>
                ))}
            </select>
            {selectedCode && (
                <div className="mt-2 text-sm text-gray-600">
                    Selected: {selectedCode}
                </div>
            )}
        </div>
    );
};

export default CountryCode;
