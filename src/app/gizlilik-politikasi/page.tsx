import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default function PrivacyPolicy() {
  return (
    <main className="min-h-screen pt-32 pb-20 bg-noir-900">
      <Navbar />
      
      <div className="container mx-auto px-6 max-w-4xl">
        <h1 className="font-serif text-4xl md:text-5xl font-bold text-white mb-10 text-center">
          Gizlilik <span className="text-gold-500">Politikası</span>
        </h1>
        
        <div className="prose prose-invert max-w-none text-noir-300 space-y-6">
          <p>Son güncelleme: 5 Nisan 2026</p>
          
          <section>
            <h2 className="text-white text-2xl font-bold mb-4">1. Veri Sorumlusu</h2>
            <p>Bu gizlilik politikası, By Boss Mimarlık Mobilya tarafından toplanan ve işlenen kişisel verilerin korunmasına yönelik bilgilendirme amacı taşımaktadır.</p>
          </section>

          <section>
            <h2 className="text-white text-2xl font-bold mb-4">2. Toplanan Veriler</h2>
            <p>Web sitemizdeki iletişim formları aracılığıyla adınız, soyadınız, e-posta adresiniz ve telefon numaranız gibi bilgiler toplanabilir. Bu bilgiler sadece size geri dönüş yapmak amacıyla kullanılır.</p>
          </section>

          <section>
            <h2 className="text-white text-2xl font-bold mb-4">3. Çerezler (Cookies)</h2>
            <p>Kullanıcı deneyimini geliştirmek için çerezler kullanmaktayız. Çerezler, web sitemizi ziyaret ettiğinizde tarayıcınıza kaydedilen küçük metin dosyalarıdır.</p>
          </section>
          
          <section>
            <h2 className="text-white text-2xl font-bold mb-4">4. Haklarınız</h2>
            <p>KVKK kapsamında verilerinizin silinmesini veya düzeltilmesini talep etme hakkınız saklıdır. Bize info@bybossmimarlik.com adresinden ulaşabilirsiniz.</p>
          </section>
        </div>
      </div>

      <Footer />
    </main>
  );
}
