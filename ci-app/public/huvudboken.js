function new_voucher_row(){
    m=document.getElementsByClassName('voucher_row');
    n=m[m.length-1];
    o=n.cloneNode(true);
    fields = o.getElementsByClassName("voucher_row_field");
    for(var i = 0; i < fields.length; i++){
        var name = fields[i].name;
        p = name.indexOf("-");
        old = name.substring(p+1);
        num = parseInt(old) +1;
        name = name.replace("-" + old, "-" + num);
        fields[i].name = name;
        fields[i].id = name;
    }
    n.getElementsByClassName('new_row_button')[0].remove();
    n.parentElement.appendChild(o);
    document.getElementById('vr_debet-' + num).value = '';
    document.getElementById('vr_kredit-' + num).value = '';
    document.getElementById('vr_account-' + num).value = '';
    document.getElementById('vr_account-' + num).focus();
}

// var $test;
// function toggle_visibility(elementId) {
//     if ((document.getElementById(elementId).style.visibility = "collapse") | ($test!="visible"))
//     {document.getElementById(elementId).style.visibility = "visible";
//         $test="visible"
//     }
//     else
//     {document.getElementById(elementId).style.visibility = "collapse";
//         $test="collapse"}
// }
// function loading(elementId){
//     document.getElementById(elementId).style.visibility = "collapse";
//     document.getElementById('spinner').style.visibility = "visible";
// }

window.onload = function(){
    let yearList = document.getElementById('booking_year_001')
    if(yearList) {
        yearList.addEventListener('change', (e) => {
            yearList.form.submit()
        });
    }

    let sieValidateSpinner = document.getElementById('sie_validate_spinner')
    if(sieValidateSpinner) {
        sieValidateSpinner.addEventListener('click', (e) => {
            sieValidateSpinner.setAttribute('aria-busy', 'true')
            document.getElementById('submit').hidden = true;
        });
    }

    let el = document.getElementById('incoming_balance_new_row_01')
    if(el) {
        el.addEventListener('click', (e) => {
            new_voucher_row();
        });
    }
    el = document.getElementById('incoming_balance_new_row_02')
    if(el) {
        el.addEventListener('click', (e) => {
            new_voucher_row();
        });
    }
    el = document.getElementById('edit_voucher_new_row_01')
    if(el) {
        el.addEventListener('click', (e) => {
            new_voucher_row();
        });
    }
    el = document.getElementById('edit_voucher_new_row_02')
    if(el) {
        el.addEventListener('click', (e) => {
            new_voucher_row();
        });
    }
    el = document.getElementById('edit_voucher_template_new_row')
    if(el) {
        el.addEventListener('click', (e) => {
            new_voucher_row();
        });
    }

    // Accounts page (Kontoplan) add/remove row logic moved from view to here
    const addBtn = document.getElementById('add-row');
    const tbody = document.getElementById('account-rows');
    const tpl = document.getElementById('row-template');
    if (addBtn && tbody && tpl) {
        let newIdx = 0;
        function bindRemove(btn){
            btn.addEventListener('click', function(){
                const tr = this.closest('tr');
                if(tr) tr.remove();
            });
        }
        addBtn.addEventListener('click', function(){
            const html = tpl.innerHTML.replaceAll('__NAME__', 'rows[new' + (newIdx++) + ']');
            const temp = document.createElement('tbody');
            temp.innerHTML = html.trim();
            const tr = temp.firstElementChild;
            tbody.appendChild(tr);
            const rmBtn = tr.querySelector('.remove-row');
            if(rmBtn) bindRemove(rmBtn);
        });
        document.querySelectorAll('.remove-row').forEach(bindRemove);
    }

    // Dimensions page add/remove row
    const addDimBtn = document.getElementById('add-dim-row');
    const dimTbody = document.getElementById('dim-rows');
    const dimTpl = document.getElementById('dim-row-template');
    if (addDimBtn && dimTbody && dimTpl) {
        let idx = 0;
        function bindRemoveDim(btn){
            btn.addEventListener('click', function(){
                const tr = this.closest('tr');
                if (tr) tr.remove();
            });
        }
        addDimBtn.addEventListener('click', function(){
            const html = dimTpl.innerHTML.replaceAll('__NAME__', 'rows[new' + (idx++) + ']');
            const temp = document.createElement('tbody');
            temp.innerHTML = html.trim();
            const tr = temp.firstElementChild;
            dimTbody.appendChild(tr);
            const rm = tr.querySelector('.remove-dim-row');
            if (rm) bindRemoveDim(rm);
        });
        document.querySelectorAll('.remove-dim-row').forEach(bindRemoveDim);
    }
}
