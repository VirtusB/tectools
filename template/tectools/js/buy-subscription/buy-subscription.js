var stripe = Stripe('pk_test_YqYxaJ8NiGPQHhVuuNpkIGca');
var elements = stripe.elements();
var form = document.getElementById('payment-form');

// CSS til kort informationer input
var style = {
    base: {
        color: "#039be5",
        fontFamily: '"Open Sans"',
        fontSmoothing: "antialiased",
        fontSize: "16px",
        "::placeholder": {
            color: "#9e9e9e"
        }
    },
    invalid: {
        color: "#fa755a",
        iconColor: "#fa755a"
    }
};


// Opret element på siden hvor brugeren kan indtaste kort indformationer
var cardElement = elements.create("card", {style: style, hidePostalCode: true});
cardElement.mount('#card-element');

// Vis fejl til brugeren, hvis de indtaster forkerte oplysninger
cardElement.on('change', function (event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});



// Tilføj en event listener, som skal oprette abonnementet når brugeren klikker køb
form.addEventListener('submit', function (ev) {
    ev.preventDefault();

    createPaymentMethod(cardElement);
});

function createPaymentMethod(card) {
    let billingName = document.getElementById('billing-name').value;
    let productID = document.getElementById('product_id').value;
    let productName = document.getElementById('product_name').value;
    let priceID = document.getElementById('price_id').value;
    let customerID = document.getElementById('customer_id').value;

    stripe
      .createPaymentMethod({
          type: 'card',
          card: card,
          billing_details: {
              name: billingName
          }
      }).then((result) => {
          if (result.error) {
              // displayError(result);
              alert('Fejl');
              console.log('Fejl');
              console.log(result);
          } else {
              createSubscription(
                  customerID,
                  result.paymentMethod.id,
                  priceID,
                  productName
              );
          }
      });
}

function createSubscription(customerId, paymentMethodId, priceId, productName) {
    showLoader('#submitBtn');

    $.post({
        url: location.origin + location.pathname,
        data: {'post_endpoint': 'newSubscription', 'customerID': customerId, 'paymentMethodID': paymentMethodId, 'priceID': priceId, 'product_name': productName},
        dataType: "json",
        cache: false,
        success: function(res) {
            console.log(res)
            location.href = '/dashboard';
        },
        error: function (err) {
            console.error(err);
        }
    });
}