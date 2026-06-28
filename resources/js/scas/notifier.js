class ScasNotifier {

    static show(message, type = 'warning') {

        const container = document.getElementById('scas-notify');

        if (!container) return;

        const id = 'msg_' + Date.now();

        container.insertAdjacentHTML('beforeend', `

            <div id="${id}"
                 class="alert alert-${type} alert-dismissible fade show shadow-sm">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                ${message}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>

            </div>

        `);

        setTimeout(()=>{

            let alerta=document.getElementById(id);

            if(alerta){

                bootstrap.Alert.getOrCreateInstance(alerta).close();

            }

        },5000);

    }

}

window.ScasNotifier = ScasNotifier;