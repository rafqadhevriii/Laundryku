const {chromium}=require('playwright');
const AxeBuilder=require('@axe-core/playwright').default;
const fs=require('fs');
const base=process.env.UIUX_BASE_URL||'http://127.0.0.1:8085';
const owner=process.env.UIUX_OWNER,cashier=process.env.UIUX_CASHIER,password=process.env.UIUX_TEST_PASSWORD;
const emptyOwner=process.env.UIUX_EMPTY_OWNER,emptyCashier=process.env.UIUX_EMPTY_CASHIER;
if(!owner||!cashier||!password||!emptyOwner||!emptyCashier)throw Error('Set synthetic UIUX_OWNER, UIUX_CASHIER, UIUX_EMPTY_OWNER, UIUX_EMPTY_CASHIER, UIUX_TEST_PASSWORD on laundryku_testing only.');
const modules={customers:'/customers',inventory:'/inventory',expenses:'/expenses',services:'/owner/services',cashiers:'/owner/cashiers',revenue:'/owner/reports',operational:'/owner/operational-reports',reconciliation:'/owner/payment-reconciliation',audit:'/owner/audit-logs',customerForm:'/customers/create',serviceForm:'/owner/services/create'};
(async()=>{
 const b=await chromium.launch({headless:true,executablePath:'/usr/bin/chromium',args:['--no-sandbox']});const results=[],errors=[];
 async function login(p,user){await p.goto(base+'/login');await p.locator('[name=username]').fill(user);await p.locator('[name=password]').fill(password);await Promise.all([p.waitForURL('**/dashboard'),p.locator('button[type=submit]').click()]);}
 async function select(p,label){await p.locator('#active-branch').selectOption({label});await Promise.all([p.waitForNavigation(),p.locator('.branch-apply').click()]);}
 async function inspect(p,role,width,key,axe=true){
  const overflow=await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth);
  const violations=axe?(await new AxeBuilder({page:p}).withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']).analyze()).violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>n.target)})):[];
  results.push({role,width,page:key,overflow,axe,violations});
  if(overflow||violations.length)console.log(JSON.stringify(results.at(-1)));
 }
 for(const width of [1280,768,375]){
  for(const role of ['owner','cashier']){
   const c=await b.newContext({viewport:{width,height:900},reducedMotion:'reduce'}),p=await c.newPage();p.on('pageerror',e=>errors.push(e.message));await login(p,role==='owner'?owner:cashier);
   if(role==='owner')await select(p,'Cabang Utama');
   for(const [key,url] of Object.entries(modules)){
    if(role==='cashier'&&!['customers','inventory','expenses','customerForm'].includes(key))continue;
    const r=await p.goto(base+url);if(r.status()!==200)throw Error(key+' status '+r.status());
    await inspect(p,role,width,key,width!==768);
    if(width!==768 && (role==='owner'||key==='inventory'))await p.screenshot({path:`docs/uiux/stage-c/screenshots/after-${width===375?'mobile':'desktop'}-${role==='cashier'?'cashier-':''}${key}.jpg`,type:'jpeg',quality:65,fullPage:true});
   }
   if(role==='cashier')for(const url of Object.values(modules).filter(u=>u.startsWith('/owner/'))){const r=await p.goto(base+url);if(r.status()!==403)throw Error('Owner route not forbidden '+url);}
   await c.close();
  }
 }
 // Empty-data pages from a separate synthetic business, not CSS-hidden rows.
 for(const [role,user] of [['owner',emptyOwner],['cashier',emptyCashier]]){
  const ec=await b.newContext({viewport:{width:375,height:900}}),ep=await ec.newPage();await login(ep,user);
  for(const [key,url] of Object.entries(modules)){
   if(role==='cashier'&&!['customers','inventory','expenses','customerForm'].includes(key))continue;
   const response=await ep.goto(base+url);if(response.status()!==200)throw Error('Empty page '+key);
   await inspect(ep,role,375,key+'-empty-business');
  }
  await ec.close();
 }
 // Actions, empty results and errors on a fresh desktop session. No real customer data.
 const c=await b.newContext({viewport:{width:1280,height:900}}),p=await c.newPage();p.on('pageerror',e=>errors.push(e.message));await login(p,owner);
 await p.goto(base+'/inventory');if(await p.locator('form[action$="/inventory"]').count())throw Error('All mode creation gate missing');
 await select(p,'Cabang Selatan');
 for(const key of ['inventory','expenses']){await p.goto(base+modules[key]);await inspect(p,'owner',1280,key+'-empty');}
 await p.goto(base+'/customers?search=NO-SYNTHETIC-MATCH');await inspect(p,'owner',1280,'customers-empty');
 await select(p,'Cabang Utama');
 // Invalid customer: native tel accepts this, server rejects and keeps remaining fields.
 await p.goto(base+'/customers/create');await p.locator('[name=name]').fill('Validation Demo');await p.locator('[name=phone]').fill('invalid');await p.locator('[name=notes]').fill('Keep customer note');
 await Promise.all([p.waitForNavigation(),p.locator('form[action$="/customers"] button[type=submit]').click()]);
 if(await p.locator('[name=name]').inputValue()!=='Validation Demo'||await p.locator('#phone-error').count()!==1)throw Error('Customer validation lost');await inspect(p,'owner',1280,'customer-invalid');
 // Service exceeds server range without imposing new client-side business rules.
 await p.goto(base+'/owner/services/create');await p.locator('[name=name]').fill('Validation Service');await p.locator('[name=price]').fill('99999999999');
 await Promise.all([p.waitForNavigation(),p.locator('form[action$="/services"] button[type=submit]').click()]);
 if(await p.locator('#price-error').count()!==1)throw Error('Service validation lost');await inspect(p,'owner',1280,'service-invalid');
 await p.goto(base+'/owner/cashiers');await p.locator('[name=name]').fill('Validation Cashier');await p.locator('[name=username]').fill(cashier);await p.locator('[name=email]').fill('duplicate-demo@example.test');await p.locator('[name=branch_id]').selectOption({label:'Cabang Utama'});await p.locator('[name=password]').fill('synthetic-only-pass');await p.locator('[name=password_confirmation]').fill('synthetic-only-pass');
 await Promise.all([p.waitForNavigation(),p.locator('form[action$="/cashiers"] button[type=submit]').click()]);
 if(await p.locator('[name=password]').inputValue()!==''||await p.locator('#username-error').count()!==1)throw Error('Cashier password or error');await inspect(p,'owner',1280,'cashier-invalid');
 await p.goto(base+'/expenses');await p.locator('[name=description]').fill('Keep expense');await p.locator('[name=amount]').fill('1000');await p.locator('[name=expense_date]').fill('2099-01-01');await Promise.all([p.waitForNavigation(),p.locator('form[action$="/expenses"] button[type=submit]').click()]);
 if(await p.locator('#expense_date-error').count()!==1)throw Error('Expense error missing');await inspect(p,'owner',1280,'expense-invalid');
 await p.goto(base+'/inventory');await p.locator('form[action$="/inventory"] [name=name]').fill('Invalid stock');await p.locator('form[action$="/inventory"] [name=unit]').fill('liter');await p.locator('[name=minimum_quantity]').fill('99999999999');await Promise.all([p.waitForNavigation(),p.locator('form[action$="/inventory"] button[type=submit]').click()]);
 if(await p.locator('#minimum_quantity-error').count()!==1)throw Error('New item validation missing');await inspect(p,'owner',1280,'inventory-create-invalid');
 await p.goto(base+'/inventory');const stock=p.locator('.stock-card').first();await stock.locator('summary').click();await stock.locator('[name=type]').selectOption('out');await stock.locator('[name=quantity]').fill('999999');await stock.locator('[name=notes]').fill('Keep movement');await Promise.all([p.waitForNavigation(),stock.locator('button[type=submit]').click()]);
 if(await p.locator('.stock-card details[open]').count()!==1||await p.locator('[name=notes][value="Keep movement"]').count()!==1)throw Error('Movement scope lost');await inspect(p,'owner',1280,'inventory-invalid');
 // Confirmation cancel issues no mutation; accept keeps submitter decision and submits exactly once.
 const review=p.locator('form[action*="/inventory/movements/"]').first();const action=await review.getAttribute('action');let posts=0;p.on('request',r=>{if(r.url()===action && r.method()==='POST')posts++});
 await review.locator('button[value=reject]').click();await p.locator('[data-confirm-cancel]').click();if(posts!==0)throw Error('Cancel submitted');
 await review.locator('button[value=reject]').click();await Promise.all([p.waitForNavigation(),p.locator('[data-confirm-accept]').click()]);if(posts!==1)throw Error('Review submitted '+posts);if(await p.locator('form[action*="/inventory/movements/"]').count())throw Error('Review not resolved');
 await p.goto(base+'/owner/payment-reconciliation');const rec=p.locator('.reconciliation-card form').first();await rec.locator('[name=decision]').selectOption('assign');await rec.locator('[name=reason]').fill('Keep reconciliation evidence');await rec.locator('button[type=submit]').click();await inspect(p,'owner',1280,'reconciliation-confirm');await Promise.all([p.waitForNavigation(),p.locator('[data-confirm-accept]').click()]);
 if(await p.locator('.reconciliation-card textarea').first().inputValue()!=='Keep reconciliation evidence')throw Error('Evidence lost');await inspect(p,'owner',1280,'reconciliation-invalid');
 const decision=p.locator('.reconciliation-card form').first();await decision.locator('[name=decision]').selectOption('unallocated');await decision.locator('button[type=submit]').click();await Promise.all([p.waitForNavigation(),p.locator('[data-confirm-accept]').click()]);if(!(await p.locator('main').textContent()).includes('Keep reconciliation evidence'))throw Error('Decision history lost');await inspect(p,'owner',1280,'reconciliation-history');
 await p.goto(base+'/owner/audit-logs');await p.locator('main details summary').first().click();await inspect(p,'owner',1280,'audit-expanded');
 // Both selected branch and legacy All filter persist on download URLs and actual endpoints.
 for(const key of ['revenue','operational']){await p.goto(base+modules[key]);const links=p.locator('.report-downloads a');for(const href of await links.evaluateAll(a=>a.map(x=>x.href))){const r=await c.request.get(href);if(r.status()!==200)throw Error('Export '+r.status());}}
 await select(p,'Semua Cabang · Ringkasan gabungan');await p.goto(base+'/owner/reports');await p.locator('[name=branch_id]').selectOption({label:'Cabang Selatan'});await Promise.all([p.waitForNavigation(),p.locator('.report-filter button[type=submit]').click()]);
 for(const href of await p.locator('.report-downloads a').evaluateAll(a=>a.map(x=>x.href))){if(!href.includes('branch_id='))throw Error('Export lost branch filter');const r=await c.request.get(href);if(r.status()!==200)throw Error('All filter export');}
 results.push({interactionChecks:['All creation gate','South empty state','customer/service/cashier/expense/stock server validation','old input isolated','password blank','review cancel and accept exactly one POST','reconciliation required shift and history','selected and All branch exports','cashier owner routes 403'],passed:true});
 await c.close();
 // Labelled scroll region is reachable and operable by keyboard on mobile.
 const kc=await b.newContext({viewport:{width:375,height:900}}),kp=await kc.newPage();await login(kp,owner);await kp.goto(base+'/owner/audit-logs');const region=kp.locator('.table-scroll[tabindex="0"]').first();await region.focus();await kp.keyboard.press('ArrowRight');await kp.waitForTimeout(180);if(await region.evaluate(el=>el.scrollLeft)<=0)throw Error('Table keyboard scroll failed');await kc.close();
 results.push({keyboardTableScroll:true,passed:true});
 await b.close();fs.writeFileSync('docs/uiux/stage-c/browser-results.json',JSON.stringify({results,errors},null,2));console.log(JSON.stringify({screens:results.filter(x=>'width'in x).length,audits:results.filter(x=>x.axe).length,violations:results.reduce((n,x)=>n+(x.violations?.length||0),0),overflow:results.filter(x=>x.overflow).length,errors}));
 if(errors.length||results.some(x=>x.overflow||x.violations?.length))throw Error('Browser/a11y checks failed; see browser-results.json');
})().catch(e=>{console.error(e);process.exit(1)});
