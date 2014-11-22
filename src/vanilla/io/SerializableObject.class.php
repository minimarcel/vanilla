<?php

// TODO créer un sérializer qui permet de sérialiser les objets
// et de les déserialiser en conservant leur class path grâce à cette interface

// TODO ou alors recréer un objet qui s'appèlerer genre Serializer
// on le construit avec un objet
// et quand on cherche à le sérialiser, il vérifie si l'objet est un SerializableObject 
// il récupère son classpath, et sérialise ce dernier avec le class path, 
// et à la déserialisation hop, il remonte tout le monde correctement

// il pourrait même avoir une méthode qui s'appelle serializeIt qui sérialize un objet et renvoie une chaîne
// et unserializeIt qui renvoie un objet en fesant abstraction du Serializer

// les objets générés depuis la base pourrait dors et déjà implémenter SerializableObject et Serializable et
// se sérialiser en passant l'id et se désérialiser en se chargeant en base de donnée

/**
 * Permet de récupérer le class path d'un objet
 * pour le sérialiser et le désérialiser.
 * Attention cette interface ne remplace pas Serializable proposée par Php5 qui permet
 * d'implémetner les méthode serialize et unserialize
 */
interface SerializableObject
{
    public function getClassPaths();
}
