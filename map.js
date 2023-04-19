import {lesMcDo} from './mcdoUnvailable.js';
import {styles} from './styles.js';


export class Map {
  constructor() {
    const annecy = { lat: 45.9, lng: 6.11 };
    this.map = new google.maps.Map(document.getElementById('map'), {
      center: annecy,
      disableDefaultUI: true,
      zoom: 11,
      styles
    });

    this.addMcdo(lesMcDo);
  }


  addMcdo(lesMcDo) {
    lesMcDo.forEach((unMcdo) => {
      let location = {
        lat : unMcdo.location.latitude,
        lng : unMcdo.location.longitude
      }
      
      let icon = 'img/mcdonalds_available.png';
      if (unMcdo.available === false) {
        icon = 'img/mcdonalds_unavailable.png';
      } 


      this.addMarker(location, unMcdo.adress, icon, unMcdo.available, unMcdo.product);

      console.log(unMcdo.adress);
      console.log(typeof(unMcdo.available) );
      console.log(unMcdo.available );

    })
  }


  addMarker(position, adresse, icon, available, product) {
    let marker = new google.maps.Marker({
      position,
      map: this.map,
      icon,
    });

    let disponibility_div; 
    if (available) {
      disponibility_div = '<p>Les McFlury sont disponible :)</p>';
    } else {
      disponibility_div = '<p>Pas de McFlurry de disponible :(</p>';
    }

    const content = 
    `<div id="content">
      <h1 id="firstHeading" class="firstHeading"> ${adresse} </h1>
      <div id="bodyContent">
        <p>Informations : </p>
        ${disponibility_div} 
        ${product} 
      </div>
    </div>`;

    const infowindow = new google.maps.InfoWindow({
      content
    });

    marker.addListener('click', () => infowindow.open(this.map, marker));
    marker.setAnimation(google.maps.Animation.DROP);
  }

} 


