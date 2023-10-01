<?php

class Defs {
    public static array $table_head = [
        'ms_vechi' => [
            'DENUMIRE' => 'denumire',
            'IN STRUCTURA' => 'in_structura',
            'FORM.JUR.' => 'form_jur',
            'JUD/SECT' => 'jud_sec',
            'LOCALITATE' => 'localitate',
            'URBAN/RURAL' => 'urban_rural',
            'ADRESA' => 'adresa',
            'AUT' => 'aut',
            'NOTA' => 'nota',
            'DATA INFIINTARII' => 'data_infiintare',
            'AUT.PRESCHIMBATA' => 'aut_preschimbata',
            'NOTA' => 'nota_1',
            'DATA' => 'data',
            'SUSPENDATA PINA LA' => 'suspendata_pana_la',
            'OFICINE LOCALE DE DISTRIBUTIE' => 'oficine_locale',
            'EXCEPTIE' => 'exceptie',
            'OBSERVATII' => 'observatii',
            'FOND COMERT' => 'fond_comert',
            'UPDATE' => 'update'
        ],
        'ms_nou' => [
            'Denumire societate' => 'denumire_societate',
            'CUI' => 'cui',
            'Localitate Sediu Social' => 'localitate_sediu_social',
            'Adresa Sediu Social' => 'adresa_sediu_social',
            'Judet sediu social' => 'judet_sediu_social',
            'Denumire PL unitate farmaceutica' => 'denumire_pl_unitate_farmaceutica',
            'Tip UNITATE' => 'tip_unitate',
            'Localitate unit farm / PL' => 'localitate_pl',
            'Urban/Rural' => 'urban_rural',
            'Adresa Farmacie' => 'adresa_farmacie',
            'Judet punct lucru' => 'judet_pl',
            'Nr. Autorizatie' => 'nr_autorizatie',
            'Data Autorizatie' => 'data_autorizare',
            'Activitate receptură/laborator' => 'activitate_receptura_laborator',
            'Mentiuni' => 'mentiuni',
            'Farmacist șef' => 'farmacist_sef',
            'Observatii' => 'observatii',
            'tel PL' => 'telefon_pl',
            'adresa email PL' => 'email_pl',
            'FOND COMERȚ' => 'fond_comert',
            'MS (DA/NU)' => 'ms',
            'CFR (DA/NU)' => 'cfr'
        ],
        'cfr' => [
            'Persoana Juridica' => 'persoana_juridica',
            'CUI' => 'cui',
            'Localitate Sediu Social' => 'localitate_sediu_social',
            'Adresa Sediu Social' => 'adresa_sediu_social',
            'Tip UNITATE' => 'tip',
            'Localitate Farmacie' => 'localitate_pl',
            'Tip Loc Farmacie' => 'urban_rural',
            'Adresa Farmacie' => 'adresa_pl',
            'Denumire Farmacie' => 'denumire_pl',
            'Nr. Autorizatie' => 'nr_autorizatie',
            'Nota' => 'nota',
            'Data Autorizatie' => 'data_autorizare',
            'Suspendare' => 'suspendare',
            'Stare' => 'stare'
        ],
        'inchise' => [
            'Denumire Societate Comerciala' => 'denumire',
            'Localitate farmacie' => 'localitate',
            'Observatii' => 'observatii'
        ],
    ];

    public static array $files = [
        'ms_vechi', 'ms_nou', 'cfr', 'inchise'
    ];

    public static array $tables = [
        'ms_vechi', 'ms_nou', 'cfr', 'inchise'
    ];

    public static array $ms_vechi_fields = [
        'A' => 'denumire',
        'B' => 'in_structura',
        'C' => 'form_jur',
        'D' => 'jud_sec',
        'E' => 'localitate',
        'F' => 'urban_rural',
        'G' => 'adresa',
        'H' => 'aut',
        'I' => 'nota',
        'J' => 'data_infiintare',
        'K' => 'aut_preschimbata',
        'L' => 'nota_1',
        'M' => 'data',
        'N' => 'suspendata_pana_la',
        'O' => '',
        'P' => 'oficine_locale',
        'Q' => 'exceptie',
        'R' => 'observatii',
        'S' => 'fond_comert',
        'T' => 'update'
    ];

    public static array $ms_nou_fields = [
        'A' => 'denumire_societate',
        'B' => 'cui',
        'C' => 'localitate_sediu_social',
        'D' => 'adresa_sediu_social',
        'E' => 'judet_sediu_social',
        'F' => 'denumire_pl_unitate_farmaceutica',
        'G' => 'tip_unitate',
        'H' => 'localitate_pl',
        'I' => 'urban_rural',
        'J' => 'adresa_farmacie',
        'K' => 'judet_pl',
        'L' => 'nr_autorizatie',
        'M' => 'data_autorizare',
        'N' => 'activitate_receptura_laborator',
        'O' => 'mentiuni',
        'P' => 'farmacist_sef',
        'Q' => 'observatii',
        'R' => 'telefon_pl',
        'S' => 'email_pl',
        'T' => 'fond_comert',
        'U' => 'ms',
        'V' => 'cfr'
    ];

    public static array $cfr_fields = [
        'A' => 'persoana_juridica',
        'B' => 'cui',
        'C' => 'localitate_sediu_social',
        'D' => 'adresa_sediu_social',
        'E' => 'tip',
        'F' => 'localitate_pl',
        'G' => 'urban_rural',
        'H' => 'adresa_pl',
        'I' => 'denumire_pl',
        'J' => 'nr_autorizatie',
        'K' => 'nota',
        'L' => 'data_autorizare',
        'M' => 'suspendare',
        'N' => 'stare'
    ];

    public static array $inchise_fields = [
        'A' => 'denumire',
        'B' => 'localitate',
        'C' => 'observatii'
    ];

    public static array $namecheck_keys = ['denumire', 'denumire_societate', 'persoana_juridica'];

    public static array $src_fields = ['persoana_juridica', 'adresa_sediu_social', 'localitate_sediu_social', 'denumire', 'localitate', 'denumire_societate', 'localitate_sediu_social', 'adresa_sediu_social', 'judet_sediu_social', 'judet_pl', 'adresa', 'localitate_pl', 'street_no'];

    public static array $address_keys = ['adresa_pl', 'adresa_farmacie', 'adresa'];

    public static array $street_fields = ['adresa_pl', 'adresa_farmacie', 'adresa'];
}