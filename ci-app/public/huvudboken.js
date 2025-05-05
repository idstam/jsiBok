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

var $test;
function toggle_visibility(elementId) {
    if ((document.getElementById(elementId).style.visibility = "collapse") | ($test!="visible"))
    {document.getElementById(elementId).style.visibility = "visible";
        $test="visible"
    }
    else
    {document.getElementById(elementId).style.visibility = "collapse";
        $test="collapse"}
}
function loading(elementId){
    document.getElementById(elementId).style.visibility = "collapse";
    document.getElementById('spinner').style.visibility = "visible";


}