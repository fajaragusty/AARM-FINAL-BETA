const ws = new WebSocket(
    "wss://ws.derivws.com/websockets/v3?app_id=1089"
);

ws.onopen = () => {

    console.log("AARM CONNECTED");

    ws.send(JSON.stringify({
        ticks: "R_25",
        subscribe: 1
    }));

};

ws.onmessage = async (event) => {

    const data = JSON.parse(event.data);

    if(data.tick){

        await fetch("/aarm/save_tick.php",{

            method:"POST",

            headers:{
                "Content-Type":"application/json"
            },

            body:JSON.stringify({
                quote:data.tick.quote,
                epoch:data.tick.epoch
            })

        });

    }

};