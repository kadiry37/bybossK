const groups: [string, string, string, string, string[]][] = [
  [
    "ic-mekan-mobilyalari",
    "İç Mekân Mobilyaları",
    "Indoor Furniture",
    "أثاث داخلي",
    []
  ],
  [
    "ofis-ticari-mobilyalar",
    "Ofis ve Ticari Mobilyalar",
    "Office & Commercial Furniture",
    "أثاث المكاتب والمنشآت التجارية",
    []
  ],
  [
    "dis-mekan-mobilyalari",
    "Dış Mekân Mobilyaları",
    "Outdoor Furniture",
    "أثاث خارجي",
    [
      "piknik-masalari",
      "picnic-tables"
    ]
  ],
  [
    "ahsap-yapilar-kaplamalar",
    "Ahşap Yapılar ve Kaplamalar",
    "Timber Structures & Cladding",
    "منشآت وكسوات خشبية",
    []
  ],
  [
    "montaj-aksesuarlar",
    "Montaj ve Sistem Aksesuarları",
    "Fittings & System Accessories",
    "ملحقات التركيب والأنظمة",
    [
      "metal-deck-klips",
      "plastik-deck-klips",
      "cam-balkon-aksesuarlari"
    ]
  ]
];

export function getCatalogGroups(lang: string) {
  return groups.map(([slug, tr, en, ar, categories]) => ({
    slug,
    label: lang === 'en' ? en : lang === 'ar' ? ar : tr,
    categories: [slug, ...categories],
  }));
}
