import { useId, useRef, useState } from 'react';
import { Search, X } from 'lucide-react';

type Entry = {
  name: string;
  slug: string;
  seriesLabel?: string;
  shortDescription?: string;
  description?: string;
  sku?: string;
  code?: string;
};

const labels = {
  tr: ['Ara', 'Site içinde ara', 'Ürün adı, kodu, proje veya hizmet', 'Kapat', 'Sonuç bulunamadı.', 'Ürün', 'Proje', 'Hizmet', 'Arama sonuçları'],
  en: ['Search', 'Search the site', 'Product name, code, project or service', 'Close', 'No results found.', 'Product', 'Project', 'Service', 'Search results'],
  ar: ['بحث', 'البحث في الموقع', 'اسم المنتج أو رمزه أو المشروع أو الخدمة', 'إغلاق', 'لا توجد نتائج.', 'منتج', 'مشروع', 'خدمة', 'نتائج البحث'],
};

function normalize(value: string) {
  return value.toLowerCase().normalize('NFKD').replace(/\p{M}/gu, '').replace(/ı/g, 'i')
    .replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
}

const compact = (value: string) => normalize(value).replaceAll(' ', '');

export default function SiteSearch({ lang, products, projects, services }: {
  lang: string;
  products: Entry[];
  projects: Entry[];
  services: Entry[];
}) {
  const [button, title, placeholder, close, empty, product, project, service, resultLabel] =
    labels[lang as keyof typeof labels] || labels.tr;
  const dialog = useRef<HTMLDialogElement>(null);
  const trigger = useRef<HTMLButtonElement>(null);
  const input = useRef<HTMLInputElement>(null);
  const id = useId();
  const [query, setQuery] = useState('');
  const normalized = normalize(query);
  const tokens = normalized ? normalized.split(' ') : [];
  const prefix = lang === 'tr' ? '' : `/${lang}`;
  const groups = [
    { entries: products, route: 'urun', label: product },
    { entries: projects, route: 'proje', label: project },
    { entries: services, route: 'hizmet', label: service },
  ];

  const results = normalized ? groups.flatMap(({ entries, route, label }) =>
    entries.flatMap(entry => {
      if (!entry.name || !entry.slug) return [];
      const codes = [entry.seriesLabel, entry.sku, entry.code].filter((code): code is string => typeof code === 'string' && Boolean(code.trim()));
      const name = normalize(entry.name);
      const codeMatch = codes.find(code => compact(code).includes(compact(normalized)));
      const text = normalize([entry.name, entry.slug, ...codes, entry.shortDescription, entry.description].join(' ').replace(/<[^>]*>/g, ' '));
      const score = codeMatch && compact(codeMatch) === compact(normalized) ? 5
        : name === normalized ? 4 : codeMatch || name.startsWith(normalized) ? 3
        : name.includes(normalized) ? 2 : tokens.every(token => text.includes(token)) ? 1 : 0;
      return score ? [{ name: entry.name, code: codeMatch || codes[0], label, score, href: `${prefix}/${route}/${encodeURIComponent(entry.slug)}/` }] : [];
    })
  ).sort((a, b) => b.score - a.score) : [];

  return (
    <>
      <button ref={trigger} type="button" aria-label={title} aria-haspopup="dialog"
        onClick={() => { dialog.current?.showModal(); input.current?.focus(); }}
        className="inline-flex h-11 min-w-11 shrink-0 items-center justify-center gap-2 rounded-full border border-[#e2c47a] bg-[#252525] px-3 text-sm font-bold text-[#f7dc94] shadow-sm transition-colors hover:bg-[#3b3832] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b48b36]">
        <Search size={18} aria-hidden="true" />
        <span className="hidden 2xl:inline">{button}</span>
      </button>
      <dialog ref={dialog} aria-labelledby={id} dir={lang === 'ar' ? 'rtl' : 'ltr'}
        onClose={() => trigger.current?.focus()}
        onClick={event => { if (event.currentTarget === event.target) dialog.current?.close(); }}
        className="m-auto max-h-[85dvh] w-[calc(100%-2rem)] max-w-xl overflow-auto rounded-2xl border border-[#dec27e] bg-[#252525] p-0 text-[#f1f0ed] shadow-2xl backdrop:bg-black/65">
        <section className="p-4 sm:p-6">
          <div className="mb-4 flex items-center justify-between gap-3">
            <h2 id={id} className="text-xl font-bold text-[#f7dc94]">{title}</h2>
            <button type="button" aria-label={close} onClick={() => dialog.current?.close()}
              className="grid h-11 w-11 place-items-center rounded-full border border-[#827c6d] hover:bg-white/10">
              <X size={20} aria-hidden="true" />
            </button>
          </div>
          <form role="search" onSubmit={event => {
            event.preventDefault();
            if (results[0]) window.location.assign(results[0].href);
          }} className="flex items-center gap-3 rounded-xl border border-[#a49f93] bg-[#363533] px-3 focus-within:border-[#f7dc94]">
            <Search size={20} aria-hidden="true" className="shrink-0 text-[#f7dc94]" />
            <input ref={input} type="search" aria-label={placeholder} placeholder={placeholder} value={query}
              onChange={event => setQuery(event.target.value)} maxLength={150}
              className="min-w-0 flex-1 bg-transparent py-3 text-base text-white outline-none placeholder:text-[#d6d4cf]" />
          </form>
          <div aria-live="polite" className="mt-3">
            {normalized && !results.length && <p className="px-2 py-3 text-[#e1dfdb]">{empty}</p>}
            {results.length > 0 && (
              <ul aria-label={resultLabel} className="divide-y divide-white/15">
                {results.map(({ href, name, label, code }) => (
                  <li key={href}>
                    <a href={href} className="block rounded-lg px-3 py-3 hover:bg-white/10 focus-visible:outline focus-visible:outline-[#f7dc94]">
                      <span className="block font-semibold text-white">{name}</span>
                      <span className="text-sm text-[#f7dc94]">{label}</span>
                      {code && <bdi className="ms-2 text-sm text-[#dedcd8]">{code}</bdi>}
                    </a>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </section>
      </dialog>
    </>
  );
}
