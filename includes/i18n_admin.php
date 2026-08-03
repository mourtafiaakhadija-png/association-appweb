<?php

$GLOBALS['labels'] = [

    'statut_projet' => [
        'en_cours' => 'قيد الإنجاز',
        'termine'  => 'منتهي',
        'suspendu' => 'معلق',
    ],

    'statut_edition' => [
        'brouillon'              => 'مسودة',
        'en_attente_validation'  => 'في انتظار المصادقة',
        'a_corriger'             => 'يتطلب تصحيحا',
        'validee'                => 'تمت المصادقة (منشورة)',
    ],

    'statut_candidature' => [
        'en_attente' => 'قيد الدراسة',
        'acceptee'   => 'مقبولة',
        'rejetee'    => 'مرفوضة',
    ],

    'role_user' => [
        'admin'         => 'مدير',
        'bureau'        => 'عضو المكتب',
        'benevole'      => 'متطوع',
        'donateur'      => 'متبرع',
        'collaborateur' => 'شريك',
    ],

    'statut_user' => [
        'actif'   => 'نشيط',
        'inactif' => 'غير نشيط',
    ],

    'cible_type' => [
        'famille'      => 'أسرة',
        'village'      => 'قرية',
        'ecole'        => 'مدرسة',
        'orphelin'     => 'يتيم',
        'malades'      => 'مرضى',
        'hafaza_quran' => 'حفظة القرآن',
        'veuves'       => 'أرامل',
    ],

];


function label(string $groupe, ?string $code): string
{
    return $GLOBALS['labels'][$groupe][$code] ?? ($code ?? '');
}