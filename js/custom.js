function f1(objButton){
    var text = document.getElementById(objButton.value);
    text.select();
    document.execCommand("copy");
}