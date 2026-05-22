import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default function TermsOfUse() {
  return (
    <main className="min-h-screen pt-32 pb-20 bg-noir-900">
      <Navbar />
      
      <div className="container mx-auto px-6 max-w-4xl">
        <h1 className="font-serif text-4xl md:text-5xl font-bold text-white mb-10 text-center">
          Kullanım <span className="text-gold-500">Şartları</span>
        </h1>
        
        <div className="prose prose-invert max-w-none text-noir-300 space-y-6">
          <p>Son güncelleme: 5 Nisan 2026</p>
          
          <section>
            <h2 className="text-white text-2xl font-bold mb-4">1. Kabul</h2>
            <p>Web sitemize erişerek ve sitemizi kullanarak, bu kullanım şartlarını ve sözleşmesini peşinen kabul etmiş sayılırsınız.</p>
          </section>

          <section>
            <h2 className="text-white text-2xl font-bold mb-4">2. İçerik ve Fikri Mülkiyet</h2>
            <p>Web sitemizde yer alan tüm görsel ve yazılı içerik By Boss Mimarlık Mobilya firmasına aittir ve izinsiz kopyalanması, paylaşılması veya ticari amaçlarla kullanılması yasaktır.</p>
          </section>

          <section>
            <h2 className="text-white text-2xl font-bold mb-4">3. Hizmet Sorumluluğu</h2>
            <p>Web sitemizde yer alan bilgilerin güncelliği ve doğruluğu için azami gayret sarf edilse de oluşabilecek teknik hatalardan firmamız sorumlu tutulamaz.</p>
          </section>
          
          <section>
            <h2 className="text-white text-2xl font-bold mb-4">4. İletişim ve Destek</h2>
            <p>Hizmetlerimizle ilgili her türlü soru, öneri ve teknik destek talebiniz için info@bybossmimarlik.com adresi üzerinden bize ulaşabilirsiniz.</p>
          </section>
        </div>
      </div>

      <Footer />
    </main>
  );
}
