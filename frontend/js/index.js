const apiRoot = "http://127.0.0.1:8000/api/";

$(document).ready(function () {
    $("#loginForm").submit(async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const jsonObject = Object.fromEntries(formData.entries());
        const jsonString = JSON.stringify(jsonObject);

        const messageBox = $("#loginMessage");
        messageBox.hide().removeClass("text-danger text-success").text("");

        try {
            const response = await fetch(`${apiRoot}login`, {
                method: "POST",
                headers: {
                    "Content-type": "application/json",
                    "Accept": "application/json"
                },
                body: jsonString
            });

            const raw = await response.text();
            console.log("Raw response:", raw);

            let result;
            try {
                result = JSON.parse(raw);
            } catch (e) {
                console.error("Failed to parse JSON:", e);
                messageBox
                    .addClass("text-danger")
                    .text("Invalid response from server.")
                    .fadeIn();
                return;
            }

            if (response.ok && result.success) {
                if (result.token) {
                    localStorage.setItem("api_token", result.token);
                    messageBox
                        .addClass("text-success")
                        .text("Login successful!")
                        .fadeIn();
                } else {
                    console.error("Missing token in response.");
                    messageBox
                        .addClass("text-danger")
                        .text("Login succeeded but token was missing.")
                        .fadeIn();
                }
            } else {
                messageBox
                    .addClass("text-danger")
                    .text(result.message || "Login failed.")
                    .fadeIn();
            }
        } catch (err) {
            console.error("Fetch error:", err);
            messageBox
                .addClass("text-danger")
                .text("Something went wrong. Please try again.")
                .fadeIn();
        }
    });
});
