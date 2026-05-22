
async function checkApi() {
  const currentLang = 'tr';
  
  console.log('Fetching products...');
  const prodRes = await fetch(`https://bybossmimarlik.com/api/products.php?lang=${currentLang}`);
  const products = await prodRes.json();
  console.log('Product 0 keys:', Object.keys(products[0]));
  console.log('Product 0 mainImage:', products[0].mainImage);
  
  console.log('Fetching navigation...');
  const navRes = await fetch(`https://bybossmimarlik.com/api/navigation.php?lang=${currentLang}`);
  const nav = await navRes.json();
  console.log('Nav data keys:', Object.keys(nav));
}

// Since I can't run fetch in this environment easily without a browser, 
// I'll just check the code again.
