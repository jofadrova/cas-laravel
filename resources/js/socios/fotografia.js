export function iniciarFotografia() {

    const input = document.getElementById("foto");

    if(!input) return;

    input.addEventListener("change", function(event){

        const reader = new FileReader();

        reader.onload = function(){

            document.getElementById("preview").src =
                reader.result;

        }

        reader.readAsDataURL(event.target.files[0]);

    });

}