-- Catégories de projets
INSERT INTO categories_projets (nom, description) VALUES
('Parrainage orphelins', 'Programmes de soutien matériel, social et éducatif aux orphelins et à leurs familles'),
('Aide alimentaire', 'Distribution de paniers alimentaires, colis et repas aux familles nécessiteuses'),
('Éducation', 'Soutien scolaire, fournitures et infrastructures éducatives'),
('Environnement / Eau', 'Creusement et équipement de puits pour les zones rurales'),
('Santé', 'Campagnes médicales et de don du sang'),
('Culturel et religieux', 'Festivals coraniques, activités religieuses et culturelles'),
('Urgence humanitaire', 'Aide d''urgence lors des vagues de froid, catastrophes naturelles'),
('Aïd al-Adha', 'Distribution de bétail sacrificiel aux familles nécessiteuses');

-- =========================================================
-- PROJETS RÉELS 
-- =========================================================

-- 1. Parrainage mensuel des orphelins
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'رافق نبيك بكفالة اليتيم',
    'Programme de parrainage mensuel des orphelins visant à soutenir la prise en charge matérielle, sociale, éducative et psychologique de l''orphelin au sein de sa famille. Chaque parrainage mensuel est fixé à 500 MAD et contribue à réduire le décrochage scolaire et à accompagner l''intégration de l''enfant dans des activités éducatives et récréatives.',
    (SELECT id FROM categories_projets WHERE nom = 'Parrainage orphelins'),
    'orphelin', '120 orphelins enregistrés, 62 familles de veuves accompagnées, région de Taroudant',
    500.00, 0.00, '2020-07-01', 'en_cours'
);

-- 2. قفة الخير - Paniers alimentaires Ramadan
-- ⚠️ Budget estimé (150 MAD/panier x 580) — à corriger avec le vrai montant si connu
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'قفة الخير',
    'Distribution de paniers alimentaires contenant les produits de première nécessité aux familles d''orphelins, de veuves et aux familles nécessiteuses des zones rurales de Taroudant, durant tout le mois de Ramadan.',
    (SELECT id FROM categories_projets WHERE nom = 'Aide alimentaire'),
    'famille', '580 paniers distribués en 2026',
    87000.00, 87000.00, '2026-03-01', 'termine'
);

-- 3. زكاة الفطر
-- ⚠️ Budget non précisé dans le magazine — à compléter
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'زكاة الفطر',
    'Collecte et distribution de la Zakat al-Fitr sous forme de colis alimentaires aux familles d''orphelins, de veuves et de nécessiteux, en partenariat avec Saphir Humanitaire, à la fin du mois de Ramadan.',
    (SELECT id FROM categories_projets WHERE nom = 'Aide alimentaire'),
    'famille', 'Familles d''orphelins, veuves et nécessiteux enregistrés à l''association',
    0.00, 0.00, '2026-03-01', 'termine'
);

-- 4. ذهب الظمأ - Iftar pour jeûneurs
-- ⚠️ Budget estimé (20 MAD/repas x 700) — à corriger
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'ذهب الظمأ',
    'Distribution de repas de rupture du jeûne (iftar) aux voyageurs et passants durant le mois de Ramadan, ainsi que l''organisation d''iftars collectifs pour les orphelins, veuves et personnes âgées d''une maison de retraite.',
    (SELECT id FROM categories_projets WHERE nom = 'Aide alimentaire'),
    'famille', '700 iftars distribués durant Ramadan 1447',
    14000.00, 14000.00, '2026-03-01', 'termine'
);

-- 5. كسوة العيد - Habits de fête pour orphelins
-- ⚠️ Budget estimé (150 MAD/tenue x 120) — à corriger
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'كسوة العيد',
    'Distribution de nouveaux habits de fête (kiswa al-Aïd) aux enfants orphelins à l''approche de l''Aïd al-Fitr, pour leur apporter joie et dignité, ainsi qu''une fête organisée avec animations et cadeaux.',
    (SELECT id FROM categories_projets WHERE nom = 'Parrainage orphelins'),
    'orphelin', '120 enfants orphelins habillés pour l''Aïd',
    18000.00, 18000.00, '2026-03-15', 'termine'
);

-- 6. تميز اليتيم - Cérémonie d'excellence annuelle
-- ⚠️ Budget non précisé — à compléter (prix : tablettes, ordinateurs, tondeuses, etc.)
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'حفل تميز اليتيم',
    'Cérémonie annuelle de reconnaissance des orphelins ayant excellé dans leur parcours scolaire, avec remise de prix (ordinateurs, tablettes, appareils électroménagers) et une sortie récréative à Agadir pour l''ensemble des enfants.',
    (SELECT id FROM categories_projets WHERE nom = 'Parrainage orphelins'),
    'orphelin', 'Orphelins scolarisés inscrits à l''association',
    0.00, 0.00, '2025-06-01', 'termine'
);

-- 7. محفظة الأمل - Cartables de la rentrée
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'محفظة الأمل',
    'Distribution de cartables et de fournitures scolaires aux enfants orphelins et issus de familles nécessiteuses à l''occasion de la rentrée scolaire, en partenariat avec des acteurs éducatifs et sociaux locaux.',
    (SELECT id FROM categories_projets WHERE nom = 'Éducation'),
    'ecole', '350 élèves bénéficiaires, rentrée scolaire 2025',
    35000.00, 35000.00, '2025-09-01', 'termine'
);

-- 8. الكتاب القرآني - Construction école coranique
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'بناء الكتاب القرآني',
    'Construction d''une école coranique (كتاب قرآني) de 500 m² incluant toutes les installations nécessaires aux activités coraniques et éducatives, offrant un cadre adapté à l''accueil et à l''hébergement des élèves. En attente des autorisations administratives nécessaires.',
    (SELECT id FROM categories_projets WHERE nom = 'Culturel et religieux'),
    'ecole', 'École coranique, superficie 500 m², région de Taroudant',
    3000000.00, 0.00, NULL, 'suspendu'
);

-- 9. المهرجان القرآني السنوي - Festival coranique "أهل القرآن"
-- ⚠️ Budget non précisé — à compléter
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'المهرجان القرآني السنوي - أهل القرآن',
    'Festival annuel récompensant les jeunes ayant mémorisé le Coran, avec récitations, interventions de oulémas, remise de certificats et de prix aux lauréats. 4ème édition organisée à ce jour.',
    (SELECT id FROM categories_projets WHERE nom = 'Culturel et religieux'),
    'ecole', 'Élèves des kouttab coraniques de la région de Taroudant',
    0.00, 0.00, '2025-01-01', 'termine'
);

-- 10.أضحية العيد- Sacrifice Aïd al-Adha
-- ⚠️ Budget estimé (1800 MAD/tête x 46) — à corriger
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'ذهب الأضحى',
    'Distribution de bétail sacrificiel (têtes de moutons/bovins) aux familles d''orphelins et nécessiteuses n''ayant pas les moyens d''acheter leur mouton de l''Aïd, incluant abattage collectif et répartition des parts de viande.',
    (SELECT id FROM categories_projets WHERE nom = 'Aïd al-Adha'),
    'famille', '46 têtes distribuées en 2024',
    82800.00, 82800.00, '2024-06-01', 'termine'
);

-- 11. سقيا الخير - Creusement de puits
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'سقيا الخير',
    'Creusement et équipement de puits d''eau potable au profit des zones rurales et montagneuses souffrant du manque d''accès à l''eau. En 2025, l''association a réalisé 10 forages et équipé intégralement 11 puits.',
    (SELECT id FROM categories_projets WHERE nom = 'Environnement / Eau'),
    'village', '11 puits équipés en 2025, zones rurales de la région de Taroudant',
    150000.00, 150000.00, '2025-01-01', 'termine'
);

-- 12. لننعم جميعا بالدفء - Caravane hivernale
-- ⚠️ Budget non précisé — à compléter
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'لننعم جميعا بالدفء',
    'Caravane solidaire de distribution de couvertures, vêtements chauds et produits alimentaires au profit des villages des zones montagneuses (Toubkal, Takouka, Sidi Abdellah Ousaid) pour faire face au froid hivernal, en partenariat avec Saphir Humanitaire.',
    (SELECT id FROM categories_projets WHERE nom = 'Urgence humanitaire'),
    'village', 'Douars de Toubkal, Takouka et Sidi Abdellah Ousaid, campagne janvier-décembre 2025',
    0.00, 0.00, '2025-01-01', 'termine'
);

-- 13. حملة التبرع بالدم - Don du sang
-- Note : campagne sans budget monétaire (don en nature = le sang), budget_prevu à 0
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'حملة التبرع بالدم',
    'Campagne de sensibilisation et de collecte de dons de sang organisée en partenariat avec le centre de transfusion sanguine de Taroudant, sous le slogan "من أحياها فكأنما أحيا الناس جميعاً". Plus de 200 donneurs mobilisés sur 2024 et 2025.',
    (SELECT id FROM categories_projets WHERE nom = 'Santé'),
    'village', '200+ donneurs en 2024-2025, ville de Taroudant',
    0.00, 0.00, '2024-01-01', 'en_cours'
);

-- 14. المخيم الصيفي - Camp d'été à Tanger
-- ⚠️ Budget non précisé — à compléter
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'المخيم الصيفي',
    'Camp d''été organisé à Tanger au profit des élèves hafiz du Coran et des orphelins, en partenariat avec un groupe de commerçants locaux. Le camp combine apprentissage, loisirs, découverte et visites de sites touristiques.',
    (SELECT id FROM categories_projets WHERE nom = 'Éducation'),
    'orphelin', 'Élèves hafiz du Coran et orphelins, séjour à Tanger',
    0.00, 0.00, '2025-07-01', 'termine'
);

-- 15. حملة طبية - Campagne médicale
-- ⚠️ Description et budget non détaillés dans le magazine — à compléter par vos soins
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'حملة طبية',
    'Campagne médicale organisée au profit des familles nécessiteuses de la région. Description à compléter avec les détails exacts (spécialités médicales, nombre de bénéficiaires, partenaires).',
    (SELECT id FROM categories_projets WHERE nom = 'Santé'),
    'famille', 'À compléter',
    0.00, 0.00, NULL, 'termine'
);

-- 16. إصلاح الكتاتيب القرآنية - Rénovation des kouttab existants
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'إصلاح الكتاتيب القرآنية',
    'Réhabilitation et équipement d''un ensemble de kouttab (écoles coraniques traditionnelles) de la province de Taroudant, afin d''améliorer les conditions d''apprentissage des élèves mémorisant le Coran.',
    (SELECT id FROM categories_projets WHERE nom = 'Culturel et religieux'),
    'ecole', 'Plusieurs kouttab de la province de Taroudant',
    0.00, 0.00, NULL, 'termine'
);

-- 17. قاعة المطالعة - Salle de lecture
-- ⚠️ Description et budget non détaillés dans le magazine — à compléter
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'قاعة المطالعة',
    'Mise en place d''une salle de lecture au profit des enfants et élèves, favorisant l''accès aux livres et le développement des habitudes de lecture. Description à compléter.',
    (SELECT id FROM categories_projets WHERE nom = 'Éducation'),
    'ecole', 'À compléter',
    0.00, 0.00, NULL, 'termine'
);

-- 18. عيدية الأيتام - Argent de poche de l'Aïd pour les orphelins
-- ⚠️ Description et budget non détaillés dans le magazine — à compléter
INSERT INTO projets (titre, description, categorie_id, cible_type, cible_details, budget_prevu, budget_collecte, date_debut, statut) VALUES
(
    'عيدية الأيتام',
    'Distribution d''une "Eidiya" (argent de poche traditionnel de l''Aïd) aux enfants orphelins, pour perpétuer cette tradition festive malgré l''absence des parents. Description à compléter.',
    (SELECT id FROM categories_projets WHERE nom = 'Parrainage orphelins'),
    'orphelin', 'À compléter',
    0.00, 0.00, NULL, 'termine'
);

-- =========================================================
-- PHOTOS DES PROJETS
-- ⚠️ Ces fichiers doivent être copiés dans le dossier uploads/images/ AVANT d'importer ce seed.sql
-- (dossier "uploads_a_copier" fourni séparément — à renommer "images" et placer dans uploads/)
-- =========================================================

INSERT INTO photos_projets (projet_id, url) VALUES
((SELECT id FROM projets WHERE titre = 'رافق نبيك بكفالة اليتيم'), 'images/kafala_yatim_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'رافق نبيك بكفالة اليتيم'), 'images/kafala_yatim_2.jpeg'),

((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_5.jpeg'),
((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_6.jpeg'),
((SELECT id FROM projets WHERE titre = 'قفة الخير'), 'images/kafat_al_khayr_7.jpeg'),

((SELECT id FROM projets WHERE titre = 'زكاة الفطر'), 'images/zakat_al_fitr_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'زكاة الفطر'), 'images/zakat_al_fitr_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'زكاة الفطر'), 'images/zakat_al_fitr_3.jpeg'),

((SELECT id FROM projets WHERE titre = 'ذهب الظمأ'), 'images/dahab_al_dhamaa_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'ذهب الظمأ'), 'images/dahab_al_dhamaa_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'ذهب الظمأ'), 'images/dahab_al_dhamaa_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'ذهب الظمأ'), 'images/dahab_al_dhamaa_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'ذهب الظمأ'), 'images/dahab_al_dhamaa_5.jpeg'),
((SELECT id FROM projets WHERE titre = 'ذهب الظمأ'), 'images/dahab_al_dhamaa_6.jpeg'),

((SELECT id FROM projets WHERE titre = 'كسوة العيد'), 'images/kiswat_al_eid_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'كسوة العيد'), 'images/kiswat_al_eid_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'كسوة العيد'), 'images/kiswat_al_eid_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'كسوة العيد'), 'images/kiswat_al_eid_4.jpeg'),

((SELECT id FROM projets WHERE titre = 'حفل تميز اليتيم'), 'images/hafl_tamayyuz_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'حفل تميز اليتيم'), 'images/hafl_tamayyuz_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'حفل تميز اليتيم'), 'images/hafl_tamayyuz_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'حفل تميز اليتيم'), 'images/hafl_tamayyuz_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'حفل تميز اليتيم'), 'images/hafl_tamayyuz_5.jpeg'),
((SELECT id FROM projets WHERE titre = 'حفل تميز اليتيم'), 'images/hafl_tamayyuz_6.jpeg'),

((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_5.jpeg'),
((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_6.jpeg'),
((SELECT id FROM projets WHERE titre = 'محفظة الأمل'), 'images/mahfada_amal_7.jpeg'),

((SELECT id FROM projets WHERE titre = 'بناء الكتاب القرآني'), 'images/kitab_qorani_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'بناء الكتاب القرآني'), 'images/kitab_qorani_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'بناء الكتاب القرآني'), 'images/kitab_qorani_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'بناء الكتاب القرآني'), 'images/kitab_qorani_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'بناء الكتاب القرآني'), 'images/kitab_qorani_5.jpeg'),

((SELECT id FROM projets WHERE titre = 'ذهب الأضحى'), 'images/dahab_al_adha_1.jpeg'),

((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_5.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_6.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_7.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_8.jpeg'),
((SELECT id FROM projets WHERE titre = 'سقيا الخير'), 'images/sakia_khayr_9.jpeg'),

((SELECT id FROM projets WHERE titre = 'لننعم جميعا بالدفء'), 'images/linanam_daf_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'لننعم جميعا بالدفء'), 'images/linanam_daf_2.jpeg'),

((SELECT id FROM projets WHERE titre = 'حملة التبرع بالدم'), 'images/tabarou_bidam_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'حملة التبرع بالدم'), 'images/tabarou_bidam_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'حملة التبرع بالدم'), 'images/tabarou_bidam_3.jpeg'),
((SELECT id FROM projets WHERE titre = 'حملة التبرع بالدم'), 'images/tabarou_bidam_4.jpeg'),
((SELECT id FROM projets WHERE titre = 'حملة التبرع بالدم'), 'images/tabarou_bidam_5.jpeg'),

((SELECT id FROM projets WHERE titre = 'المخيم الصيفي'), 'images/mokhayam_saifi_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'المخيم الصيفي'), 'images/mokhayam_saifi_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'المخيم الصيفي'), 'images/mokhayam_saifi_3.jpeg'),

((SELECT id FROM projets WHERE titre = 'حملة طبية'), 'images/hamla_tibbiya_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'حملة طبية'), 'images/hamla_tibbiya_2.jpeg'),

((SELECT id FROM projets WHERE titre = 'إصلاح الكتاتيب القرآنية'), 'images/islah_katatib_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'إصلاح الكتاتيب القرآنية'), 'images/islah_katatib_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'إصلاح الكتاتيب القرآنية'), 'images/islah_katatib_3.jpeg'),

((SELECT id FROM projets WHERE titre = 'قاعة المطالعة'), 'images/qaat_mutalaa_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'قاعة المطالعة'), 'images/qaat_mutalaa_2.jpeg'),

((SELECT id FROM projets WHERE titre = 'عيدية الأيتام'), 'images/eidiya_1.jpeg'),
((SELECT id FROM projets WHERE titre = 'عيدية الأيتام'), 'images/eidiya_2.jpeg'),
((SELECT id FROM projets WHERE titre = 'عيدية الأيتام'), 'images/eidiya_3.jpeg');
