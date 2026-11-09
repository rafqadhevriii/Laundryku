const {chromium}=require('playwright');
const AxeBuilder=require('@axe-core/playwright').default;
const fs=require('fs');
const base=process.env.BRANCH_BASE_URL||'http://127.0.0.1:8085';
const owner=process.env.BRANCH_OWNER,cashier=process.env.BRANCH_CASHIER,password=process.env.BRANCH_TEST_PASSWORD;
if(!owner||!cashier||!password)throw Error('Set BRANCH_OWNER, BRANCH_CASHIER, BRANCH_TEST_PASSWORD for isolated testing fixtures.');
(async()=>{
 const browser=await chromium.launch({executablePath:'/usr/bin/chromium',headless:true,args:['--no-sandbox']});
 const results=[];
 for(const mobile of [false,true]){
  const context=await browser.newContext({viewport:mobile?{width:390,height:844}:{width:1440,height:1000},reducedMotion:'reduce'});
  const page=await context.newPage();let errors=[];page.on('pageerror',e=>errors.push(e.message));
  const prefix=mobile?'mobile':'desktop';
  async function shot(name){await page.screenshot({path:`docs/branch-context/screenshots/${prefix}-${name}.jpg`,type:'jpeg',quality:78,fullPage:true});}
  async function audit(name){
   const report=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']).analyze();
   const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth);
   results.push({viewport:prefix,page:name,violations:report.violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>n.target)})),horizontalOverflow:overflow});
  }
  async function login(username){await page.goto(base+'/login');await page.locator('[name=username]').fill(username);await page.locator('[name=password]').fill(password);await Promise.all([page.waitForURL('**/dashboard'),page.locator('button[type=submit]').click()]);}
  await login(owner);await shot('all-dashboard');await audit('all-dashboard');
  const options=await page.locator('#active-branch option').evaluateAll(opts=>opts.map(o=>({value:o.value,label:o.label})));
  const a=options.find(o=>o.label.includes('Utama')),b=options.find(o=>o.label.includes('Selatan'));
  if(!a||!b)throw Error(JSON.stringify(options));
  await page.goto(base+'/orders/create');
  if(await page.locator('[name=customer_id]').count())throw Error('All mode unexpectedly has order form');
  await shot('all-order-gate');await audit('all-order-gate');
  // A real browser request without CSRF must fail, even with an authenticated session.
  const csrf=await page.evaluate(async()=>{const r=await fetch('/workspace/branch',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'active_branch=all'});return r.status;});
  if(csrf!==419)throw Error('CSRF expected419 got'+csrf);
  await page.goto(base+'/dashboard');
  async function select(id){await page.locator('#active-branch').selectOption(id);await Promise.all([page.waitForNavigation(),page.locator('.branch-apply').click()]);}
  await select(a.value);await shot('branch-a-dashboard');await audit('branch-a-dashboard');
  await page.goto(base+'/orders');
  if(await page.locator('#active-branch').inputValue()!==a.value)throw Error('Context lost');
  await page.goto(base+'/orders/create');await select(b.value);
  if(await page.locator('main [name=branch_id]').count())throw Error('Duplicate branch selector');
  const customers=await page.locator('[name=customer_id] option').allTextContents();
  if(!customers.some(s=>s.includes('Selatan')))throw Error('South customer missing '+customers);
  await shot('branch-b-order');await audit('branch-b-order');
  // Keyboard-only selection can reach and operate both native select and submit.
  await page.locator('#active-branch').focus();await page.keyboard.press('Home');await page.keyboard.press('Tab');
  if(!(await page.locator('.branch-apply').evaluate(el=>el===document.activeElement)))throw Error('Keyboard focus not on apply');
  await Promise.all([page.waitForNavigation(),page.keyboard.press('Enter')]);
  if(await page.locator('#active-branch').inputValue()!=='all')throw Error('Keyboard switch failed');
  // Log out using a CSRF-protected POST with the current token.
  await Promise.all([page.waitForURL('**/login'),page.locator('form[action$="/logout"] button:visible').first().click()]);
  await login(cashier);
  if(await page.locator('#active-branch').count())throw Error('Cashier has switcher');
  await shot('cashier-dashboard');await audit('cashier-dashboard');
  if(errors.length)throw Error(errors.join('\n'));
  results.push({viewport:prefix,checks:['CSRF 419','All mode creation gate','context persists across pages','B customer filter','no duplicate order branch field','keyboard switch','cashier assignment only','no JavaScript errors'],passed:true});
  await context.close();
 }
 await browser.close();fs.writeFileSync('docs/branch-context/browser-results.json',JSON.stringify(results,null,2));
 console.log(JSON.stringify(results,null,2));
})().catch(e=>{console.error(e);process.exit(1)});
