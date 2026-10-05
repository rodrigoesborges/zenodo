import{c as s,g as a,t as o}from"./translation-DoG5ZELJ-Bhg2nlZh.js";/**
 * @copyright Copyright (c) 2026
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */const d=document.getElementById("sandboxtoken"),c=document.getElementById("productiontoken"),l=document.getElementById("tokensubmit"),n=document.getElementById("tokensubmit-status"),e=(t,r)=>{n.textContent=t,n.className=r},u=async()=>{try{const t=await s.get(a("/apps/zenodo/settings"));d.value=t.data.tokenSandbox??"",c.value=t.data.tokenProduction??""}catch{e(o("zenodo","Failed to load the current credentials."),"error")}};l.addEventListener("click",async()=>{e("","");try{await s.post(a("/apps/zenodo/settings"),{token_sandbox:d.value,token_production:c.value}),e(o("zenodo","Credentials stored."),"success")}catch{e(o("zenodo","Failed to store the credentials."),"error")}});u();
